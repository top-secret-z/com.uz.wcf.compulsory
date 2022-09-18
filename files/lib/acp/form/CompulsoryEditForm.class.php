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
namespace wcf\acp\form;

use wcf\data\compulsory\Compulsory;
use wcf\data\compulsory\CompulsoryAction;
use wcf\data\compulsory\CompulsoryEditor;
use wcf\data\package\PackageCache;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows the compulsory edit form.
 */
class CompulsoryEditForm extends CompulsoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.compulsory.list';

    /**
     * Compulsory data
     */
    public $compulsoryID = 0;

    public $compulsory;

    public $compulsoryIDs = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->compulsoryID = \intval($_REQUEST['id']);
        }
        $this->compulsory = new Compulsory($this->compulsoryID);
        if (!$this->compulsory->compulsoryID) {
            throw new IllegalLinkException();
        }

        if ($this->compulsory->isMultilingual) {
            $this->isMultilingual = 1;
        }
        $this->activationTime = $this->compulsory->activationTime;

        if (!WCF::getSession()->getPermission('admin.user.canManageCompulsory')) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            I18nHandler::getInstance()->setOptions(
                'title',
                PackageCache::getInstance()->getPackageID('com.uz.wcf.compulsory'),
                $this->compulsory->title,
                'wcf.acp.compulsory.title\d+'
            );
            $this->title = $this->compulsory->title;

            $this->addNewUser = $this->compulsory->addNewUser;
            $this->isDisabled = $this->compulsory->isDisabled;
            $this->isRefusable = $this->compulsory->isRefusable;

            $this->acceptAddGroupIDs = \unserialize($this->compulsory->acceptAddGroupIDs);
            $this->acceptRemoveGroupIDs = \unserialize($this->compulsory->acceptRemoveGroupIDs);
            $this->acceptUserAction = $this->compulsory->acceptUserAction;
            $this->acceptUrl = $this->compulsory->acceptUrl;
            $this->refuseAddGroupIDs = \unserialize($this->compulsory->refuseAddGroupIDs);
            $this->refuseRemoveGroupIDs = \unserialize($this->compulsory->refuseRemoveGroupIDs);
            $this->refuseUserAction = $this->compulsory->refuseUserAction;
            $this->refuseUrl = $this->compulsory->refuseUrl;
            $this->pages = $this->compulsory->pages;

            $this->hasPeriod = $this->compulsory->hasPeriod;

            if ($this->compulsory->periodEnd) {
                $dateTime = DateUtil::getDateTimeByTimestamp($this->compulsory->periodEnd);
                $dateTime->setTimezone(WCF::getUser()->getTimeZone());
                $this->periodEnd = $dateTime->format('c');
            }
            if ($this->compulsory->periodStart) {
                $dateTime = DateUtil::getDateTimeByTimestamp($this->compulsory->periodStart);
                $dateTime->setTimezone(WCF::getUser()->getTimeZone());
                $this->periodStart = $dateTime->format('c');
            }

            // content
            foreach ($this->compulsory->getCompulsoryContents() as $languageID => $content) {
                $this->subject[$languageID] = $content->subject;
                $this->content[$languageID] = $content->content;
            }

            // conditions
            $conditions = $this->compulsory->getConditions();
            foreach ($conditions as $condition) {
                $this->conditions[$condition->getObjectType()->conditiongroup][$condition->objectTypeID]->getProcessor()->setData($condition);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'action' => 'edit',
            'compulsory' => $this->compulsory,
            'compulsoryID' => $this->compulsory->compulsoryID,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        $this->title = 'wcf.acp.compulsory.title' . $this->compulsory->compulsoryID;
        if (I18nHandler::getInstance()->isPlainValue('title')) {
            I18nHandler::getInstance()->remove($this->title);
            $this->title = I18nHandler::getInstance()->getValue('title');
        } else {
            I18nHandler::getInstance()->save('title', $this->title, 'wcf.acp.compulsory', PackageCache::getInstance()->getPackageID('com.uz.wcf.compulsory'));
        }

        $content = [];
        if ($this->isMultilingual) {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $content[$language->languageID] = [
                    'subject' => !empty($this->subject[$language->languageID]) ? $this->subject[$language->languageID] : '',
                    'content' => !empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : '',
                    'htmlInputProcessor' => $this->htmlInputProcessors[$language->languageID] ?? null,
                ];
            }
        } else {
            $content[0] = [
                'subject' => !empty($this->subject[0]) ? $this->subject[0] : '',
                'content' => !empty($this->content[0]) ? $this->content[0] : '',
                'htmlInputProcessor' => $this->htmlInputProcessors[0] ?? null,
            ];
        }

        $data = [
            'activationTime' => $this->activationTime,
            'addNewUser' => $this->addNewUser,
            'isDisabled' => $this->isDisabled,
            'isRefusable' => $this->isRefusable,
            'time' => TIME_NOW,
            'title' => $this->title,
            'userID' => WCF::getUser()->userID,
            'username' => WCF::getUser()->username,

            'acceptAddGroupIDs' => \serialize($this->acceptAddGroupIDs),
            'acceptRemoveGroupIDs' => \serialize($this->acceptRemoveGroupIDs),
            'acceptUserAction' => $this->acceptUserAction,
            'acceptUrl' => $this->acceptUrl,
            'refuseAddGroupIDs' => \serialize($this->refuseAddGroupIDs),
            'refuseRemoveGroupIDs' => \serialize($this->refuseRemoveGroupIDs),
            'refuseUserAction' => $this->refuseUserAction,
            'refuseUrl' => $this->refuseUrl,
            'pages' => $this->pages,

            'hasPeriod' => $this->hasPeriod,
            'periodEnd' => $this->hasPeriod ? $this->periodEndObj->getTimestamp() : 0,
            'periodStart' => $this->hasPeriod ? $this->periodStartObj->getTimestamp() : 0,
        ];

        // activation
        if (!$this->activationTime && !$this->isDisabled) {
            $data['activationTime'] = TIME_NOW;
        }

        $this->objectAction = new CompulsoryAction([$this->compulsory], 'update', ['data' => \array_merge($this->additionalFields, $data), 'content' => $content]);
        $this->objectAction->executeAction();

        $compulsoryEditor = new CompulsoryEditor(new Compulsory($this->compulsory->compulsoryID));

        // transform conditions array into one-dimensional array
        $conditions = [];
        foreach ($this->conditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }

        ConditionHandler::getInstance()->updateConditions($this->compulsory->compulsoryID, $this->compulsory->getConditions(), $conditions);

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();

        $this->saved();

        // show success
        WCF::getTPL()->assign(['success' => true]);
    }
}
