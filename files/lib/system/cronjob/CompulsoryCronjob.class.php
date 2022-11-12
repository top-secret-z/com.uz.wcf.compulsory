<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wcf\system\cronjob;

use wcf\data\compulsory\CompulsoryEditor;
use wcf\data\compulsory\CompulsoryList;
use wcf\data\cronjob\Cronjob;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\WCF;

/**
 * Cronjob for Compulsory Topics
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
        //    $compulsoryList->getConditionBuilder()->add('hasPeriod = ?', [1]);
        $compulsoryList->readObjects();
        $compulsories = $compulsoryList->getObjects();
        if (!\count($compulsories)) {
            return;
        }

        foreach ($compulsories as $compulsory) {
            if ($compulsory->hasPeriod) {
                if ($compulsory->isDisabled && $compulsory->periodStart < TIME_NOW && $compulsory->periodEnd > TIME_NOW) {
                    $editor = new CompulsoryEditor($compulsory);
                    $editor->update([
                        'isDisabled' => 0,
                        'activationTime' => TIME_NOW,
                    ]);
                }

                if (!$compulsory->isDisabled && $compulsory->periodEnd < TIME_NOW) {
                    $editor = new CompulsoryEditor($compulsory);
                    $editor->update([
                        'isDisabled' => 1,
                    ]);
                }
            }

            // update stats
            $sql = "SELECT COUNT(*) as count
                    FROM     wcf" . WCF_N . "_compulsory_dismissed
                    WHERE    compulsoryID = ? AND choice LIKE ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsory->compulsoryID, 'refuse']);
            $refuse = $statement->fetchColumn();

            $sql = "SELECT COUNT(*) as count
                    FROM     wcf" . WCF_N . "_compulsory_dismissed
                    WHERE    compulsoryID = ? AND choice LIKE ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsory->compulsoryID, 'accept']);
            $accept = $statement->fetchColumn();

            $editor = new CompulsoryEditor($compulsory);
            $editor->update([
                'statAccept' => $accept,
                'statRefuse' => $refuse,
            ]);
        }

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();
    }
}
