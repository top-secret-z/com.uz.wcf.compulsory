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
use wcf\data\compulsory\dismissed\CompulsoryDismissedList;
use wcf\data\user\UserList;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the compulsory stats page
 */
class CompulsoryStatsPage extends AbstractPage
{
    /**
     * Compulsory / stats data
     */
    public $compulsory;

    public $acceptList;

    public $refuseList;

    public $remainingUsers = [];

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
    public function readParameters()
    {
        parent::readParameters();

        if (!isset($_REQUEST['id'])) {
            throw new IllegalLinkException();
        }

        if (isset($_REQUEST['id'])) {
            $id = \intval($_REQUEST['id']);
        }
        $this->compulsory = new Compulsory($id);
        if (!$this->compulsory->compulsoryID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // get users who accepted / refused
        $dismissedList = new CompulsoryDismissedList();
        $dismissedList->getConditionBuilder()->add('userID IS NOT NULL');
        $dismissedList->getConditionBuilder()->add('choice = ?', ['accept']);
        $dismissedList->getConditionBuilder()->add('compulsoryID = ?', [$this->compulsory->compulsoryID]);
        $dismissedList->sqlOrderBy = 'time ASC';
        if (!USER_COMPULSORY_STATS_OLD) {
            $dismissedList->sqlOrderBy = 'time DESC';
            $dismissedList->sqlLimit = 5;
        }
        $dismissedList->readObjects();
        $this->acceptList = $dismissedList->getObjects();

        $dismissedList = new CompulsoryDismissedList();
        $dismissedList->getConditionBuilder()->add('userID IS NOT NULL');
        $dismissedList->getConditionBuilder()->add('choice = ?', ['refuse']);
        $dismissedList->getConditionBuilder()->add('compulsoryID = ?', [$this->compulsory->compulsoryID]);
        $dismissedList->sqlOrderBy = 'time ASC';
        if (!USER_COMPULSORY_STATS_OLD) {
            $dismissedList->sqlOrderBy = 'time DESC';
            $dismissedList->sqlLimit = 5;
        }
        $dismissedList->readObjects();
        $this->refuseList = $dismissedList->getObjects();

        // Get remaining users
        $userList = new UserList();

        // conditions
        $conditions = $this->compulsory->getConditions();
        foreach ($conditions as $condition) {
            $condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
        }

        // include new users
        if (!$this->compulsory->addNewUser) {
            $userList->getConditionBuilder()->add('user_table.registrationDate < ?', [$this->compulsory->activationTime]);
        }

        // accepted / refused
        $userList->getConditionBuilder()->add(
            'user_table.userID NOT IN (SELECT userID FROM wcf' . WCF_N . '_compulsory_dismissed WHERE userID IS NOT NULL AND compulsoryID = ?)',
            [$this->compulsory->compulsoryID]
        );

        if (!USER_COMPULSORY_STATS_OLD) {
            $userList->sqlLimit = 5;
        }

        $userList->sqlOrderBy = 'username ASC';
        $userList->readObjects();

        $this->remainingUsers = $userList->getObjects();
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        // assign parameters
        WCF::getTPL()->assign([
            'compulsory' => $this->compulsory,
            'acceptCount' => USER_COMPULSORY_STATS_OLD ? \count($this->acceptList) : $this->compulsory->getNumberAccepted(),
            'acceptCountDeleted' => $this->compulsory->getNumberAcceptedDeleted(),
            'acceptUsers' => $this->acceptList,
            'refuseCount' => USER_COMPULSORY_STATS_OLD ? \count($this->refuseList) : $this->compulsory->getNumberRefused(),
            'refuseCountDeleted' => $this->compulsory->getNumberRefusedDeleted(),
            'refuseUsers' => $this->refuseList,
            'remainingCount' => USER_COMPULSORY_STATS_OLD ? \count($this->remainingUsers) : $this->compulsory->getNumberRemaining(),
            'remainingUsers' => $this->remainingUsers,
            'totalCount' => $this->compulsory->getNumberTotal(),
        ]);
    }
}
