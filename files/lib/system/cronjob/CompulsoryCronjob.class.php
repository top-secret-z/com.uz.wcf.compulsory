<?php

namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\data\compulsory\CompulsoryEditor;
use wcf\data\compulsory\CompulsoryList;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\cronjob\AbstractCronjob;
use wcf\system\WCF;

/**
 * Cronjob for Compulsory Topics
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // only if configured
        if (!MODULE_COMPULSORY) {
            return;
        }

        // enable / disable compulsories
        $compulsoryList = new CompulsoryList();
        //	$compulsoryList->getConditionBuilder()->add('hasPeriod = ?', [1]);
        $compulsoryList->readObjects();
        $compulsories = $compulsoryList->getObjects();
        if (!count($compulsories)) {
            return;
        }

        foreach ($compulsories as $compulsory) {
            if ($compulsory->hasPeriod) {
                if ($compulsory->isDisabled && $compulsory->periodStart < TIME_NOW && $compulsory->periodEnd > TIME_NOW) {
                    $editor = new CompulsoryEditor($compulsory);
                    $editor->update([
                                        'isDisabled' => 0,
                                        'activationTime' => TIME_NOW
                                    ]);
                }

                if (!$compulsory->isDisabled && $compulsory->periodEnd < TIME_NOW) {
                    $editor = new CompulsoryEditor($compulsory);
                    $editor->update([
                                        'isDisabled' => 1
                                    ]);
                }
            }

            // update stats
            $sql = "SELECT COUNT(*) as count
					FROM 	wcf" . WCF_N . "_compulsory_dismissed
					WHERE	compulsoryID = ? AND choice LIKE ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsory->compulsoryID, 'refuse']);
            $refuse = $statement->fetchColumn();

            $sql = "SELECT COUNT(*) as count
					FROM 	wcf" . WCF_N . "_compulsory_dismissed
					WHERE	compulsoryID = ? AND choice LIKE ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsory->compulsoryID, 'accept']);
            $accept = $statement->fetchColumn();

            $editor = new CompulsoryEditor($compulsory);
            $editor->update([
                                'statAccept' => $accept,
                                'statRefuse' => $refuse
                            ]);
        }

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();
    }
}
