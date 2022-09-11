<?php

namespace wcf\system\compulsory;

use wcf\data\compulsory\Compulsory;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles compulsory-related matters.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryHandler extends SingletonFactory
{
    /**
     * array with all active compulsories
     */
    protected $compulsories = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->compulsories = CompulsoryCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns the compulsories which are visible for the active user.
     */
    public function getVisibleCompulsories()
    {
        $compulsories = [];
        foreach ($this->compulsories as $compulsory) {
            if ($compulsory->isDismissed()) {
                continue;
            }

            $conditions = $compulsory->getConditions();
            foreach ($conditions as $condition) {
                if (!$condition->getObjectType()->getProcessor()->showContent($condition)) {
                    continue 2;
                }
            }

            $compulsories[$compulsory->compulsoryID] = $compulsory;
        }

        return $compulsories;
    }
}
