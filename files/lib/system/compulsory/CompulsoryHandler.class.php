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
namespace wcf\system\compulsory;

use wcf\data\compulsory\Compulsory;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles compulsory-related matters.
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
