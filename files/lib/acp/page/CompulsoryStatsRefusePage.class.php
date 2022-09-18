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
namespace wcf\acp\page;

use wcf\data\compulsory\Compulsory;
use wcf\data\user\UserProfileList;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the compulsory stats page
 */
class CompulsoryStatsRefusePage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.compulsory.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_COMPULSORY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageCompulsory'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 50;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'username';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['username', 'registrationDate', 'lastActivityTime', 'activityPoints', 'likesReceived', 'trophyPoints'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = UserProfileList::class;

    // compulsory
    public $compulsory;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }

        if (!empty($_REQUEST['id'])) {
            $id = \intval($_REQUEST['id']);
            $this->compulsory = new Compulsory($id);
            if (!$this->compulsory->compulsoryID) {
                throw new IllegalLinkException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->getConditionBuilder()->add(
            'user_table.userID IN (SELECT userID FROM wcf' . WCF_N . '_compulsory_dismissed WHERE userID IS NOT NULL AND choice LIKE ? AND compulsoryID = ?)',
            ['refuse', $this->compulsory->compulsoryID]
        );
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'compulsory' => $this->compulsory,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validateSortField()
    {
        // protect email
        if (WCF::getSession()->getPermission('admin.user.canEditMailAddress')) {
            $this->validSortFields[] = 'email';
        }

        parent::validateSortField();
    }
}
