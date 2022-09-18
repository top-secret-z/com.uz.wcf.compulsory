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
namespace wcf\action;

use wcf\data\compulsory\Compulsory;
use wcf\data\compulsory\CompulsoryEditor;
use wcf\data\compulsory\content\CompulsoryContent;
use wcf\data\page\PageCache;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserAction;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Allows the user to accept a compulsory.
 */
class CompulsoryAcceptAction extends AbstractSecureAction
{
    /**
     * instance of the compulsory with the given id
     */
    public $compulsory;

    /**
     * @inheritDoc
     */
    public $loginRequired = true;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_GET['id'])) {
            $compulsoryID = \intval($_GET['id']);
        }
        $this->compulsory = new Compulsory($compulsoryID);
        if (!$this->compulsory->compulsoryID) {
            throw new IllegalLinkException();
        }
    }

    public function execute()
    {
        parent::execute();

        // just in case
        if (!WCF::getUser()->userID) {
            return;
        }

        $user = WCF::getUser();

        // update log
        $sql = "INSERT INTO    wcf" . WCF_N . "_compulsory_dismissed
                    (compulsoryID, choice, time, userID, username)
                VALUES (?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->compulsory->compulsoryID, 'accept', TIME_NOW, $user->userID, $user->username]);

        $editor = new CompulsoryEditor($this->compulsory);
        $editor->updateCounters(['statAccept' => 1]);

        // reset userStorage
        UserStorageHandler::getInstance()->reset([$user->userID], 'dismissedCompulsories');

        // execute user actions
        switch ($this->compulsory->acceptUserAction) {
            case 'ban':
                $language = WCF::getUser()->getLanguage();
                $content = CompulsoryContent::getCompulsoryContent($this->compulsory->compulsoryID, $language->languageID);
                $userAction = new UserAction([$user], 'ban', [
                    'banExpires' => 0,
                    'banReason' => $language->getDynamicVariable('wcf.user.compulsory.ban.accept', [
                        'subject' => $content->subject,
                    ]),
                ]);
                $userAction->executeAction();
                break;
            case 'disable':
                $userAction = new UserAction([$user], 'disable');
                $userAction->executeAction();
                break;
            case 'enable':
                $userAction = new UserAction([$user], 'enable');
                $userAction->executeAction();
                break;
        }

        // change groups if configured, no admin group operation allowed
        $groupIDs = $this->getAllowedGroupIDs($this->compulsory->acceptAddGroupIDs);
        if (\count($groupIDs)) {
            $action = new UserAction([$user->userID], 'addToGroups', [
                'groups' => $groupIDs,
                'deleteOldGroups' => false,
                'addDefaultGroups' => false,
            ]);
            $action->executeAction();
        }

        $groupIDs = $this->getAllowedGroupIDs($this->compulsory->acceptRemoveGroupIDs);
        if (\count($groupIDs)) {
            $action = new UserAction([$user->userID], 'removeFromGroups', [
                'groups' => $groupIDs,
            ]);
            $action->executeAction();
        }

        // Redirect to configured or main page
        if (!empty($this->compulsory->acceptUrl)) {
            HeaderUtil::redirect($this->compulsory->acceptUrl);
        } else {
            $url = StringUtil::trim(USER_COMPULSORY_URL);
            if (empty($url)) {
                $page = PageCache::getInstance()->getLandingPage();
                $url = $page->getLink();
            }
            HeaderUtil::redirect($url);
        }

        exit;
    }

    /**
     * get allowed groupIDs
     */
    public function getAllowedGroupIDs($groupIDs)
    {
        $groupIDs = \unserialize($groupIDs);
        if (empty($groupIDs)) {
            return [];
        }

        $allowedUserGroupIDs = [];
        foreach (UserGroup::getGroupsByIDs($groupIDs) as $group) {
            if (!$group->isAdminGroup()) {
                $allowedUserGroupIDs[] = $group->groupID;
            }
        }

        return \array_intersect($groupIDs, $allowedUserGroupIDs);
    }
}
