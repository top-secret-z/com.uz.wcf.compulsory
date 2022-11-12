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

use DateTime;
use wcf\data\compulsory\CompulsoryAction;
use wcf\data\compulsory\CompulsoryEditor;
use wcf\data\language\Language;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\PackageCache;
use wcf\data\user\group\UserGroup;
use wcf\form\AbstractForm;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Shows the compulsory add form.
 */
class CompulsoryAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.compulsory.add';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_COMPULSORY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageCompulsory'];

    /**
     * compulsory and related data
     */
    public $content = [];

    public $activationTime = 0;

    public $addNewUser = 1;

    public $hasPeriod = 0;

    public $isDisabled = 1;

    public $isRefusable = 0;

    public $subject = [];

    public $title = '';

    public $acceptAddGroupIDs = [];

    public $acceptRemoveGroupIDs = [];

    public $acceptUserAction = 'none';

    public $acceptUrl = '';

    public $refuseAddGroupIDs = [];

    public $refuseRemoveGroupIDs = [];

    public $refuseUserAction = 'none';

    public $refuseUrl = '';

    public $pages = '';

    /**
     * period (ISO 8601)
     * @var    string / dateTime
     */
    public $periodEnd = '';

    public $periodEndObj;

    public $periodStart = '';

    public $periodStartObj;

    /**
     * true if multi-lingual
     */
    public $isMultilingual = 0;

    /**
     * @var HtmlInputProcessor[]
     */
    public $htmlInputProcessors = [];

    /**
     * list of available languages
     */
    public $availableLanguages = [];

    /**
     * list of grouped user group assignment condition object types
     */
    public $conditions = [];

    /**
     * available user groups
     */
    public $groups = [];

    /**
     * @inheritDoc
     */
    public function readData()
    {
        // conditions
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.uz.wcf.compulsory.condition');
        foreach ($objectTypes as $objectType) {
            if (!$objectType->conditiongroup) {
                continue;
            }

            if (!isset($groupedObjectTypes[$objectType->conditiongroup])) {
                $groupedObjectTypes[$objectType->conditiongroup] = [];
            }

            $groupedObjectTypes[$objectType->conditiongroup][$objectType->objectTypeID] = $objectType;
        }
        $this->conditions = $groupedObjectTypes;

        // get accessible groups, exclude admin/owner group (no OWNER in 3.1)
        $this->groups = UserGroup::getAccessibleGroups([], [UserGroup::GUESTS, UserGroup::EVERYONE, UserGroup::USERS]);
        foreach ($this->groups as $key => $group) {
            if ($group->isAdminGroup()) {
                unset($this->groups[$key]);
            }
        }

        parent::readData();
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        // get available languages
        $this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
        if (\count($this->availableLanguages)) {
            $this->isMultilingual = 1;
        }

        // Register multilingual fields
        I18nHandler::getInstance()->register('title');
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'availableLanguages' => $this->availableLanguages,
            'groups' => $this->groups,
            'groupedObjectTypes' => $this->conditions,

            'activationTime' => $this->activationTime,
            'addNewUser' => $this->addNewUser,
            'content' => $this->content,
            'isDisabled' => $this->isDisabled,
            'isMultilingual' => $this->isMultilingual,
            'isRefusable' => $this->isRefusable,
            'subject' => $this->subject,

            'hasPeriod' => $this->hasPeriod,
            'periodEnd' => $this->periodEnd,
            'periodStart' => $this->periodStart,

            'acceptAddGroupIDs' => $this->acceptAddGroupIDs,
            'acceptRemoveGroupIDs' => $this->acceptRemoveGroupIDs,
            'acceptUserAction' => $this->acceptUserAction,
            'acceptUrl' => $this->acceptUrl,
            'refuseAddGroupIDs' => $this->refuseAddGroupIDs,
            'refuseRemoveGroupIDs' => $this->refuseRemoveGroupIDs,
            'refuseUserAction' => $this->refuseUserAction,
            'refuseUrl' => $this->refuseUrl,
            'pages' => $this->pages,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        // Read i18n values
        I18nHandler::getInstance()->readValues();
        if (I18nHandler::getInstance()->isPlainValue('title')) {
            $this->title = I18nHandler::getInstance()->getValue('title');
        }

        $this->addNewUser = $this->hasPeriod = $this->isDisabled = $this->isRefusable = 0;
        if (isset($_POST['addNewUser'])) {
            $this->addNewUser = 1;
        }
        if (isset($_POST['hasPeriod'])) {
            $this->hasPeriod = 1;
        }
        if (isset($_POST['isDisabled'])) {
            $this->isDisabled = 1;
        }
        if (isset($_POST['isRefusable'])) {
            $this->isRefusable = 1;
        }
        if (isset($_POST['content']) && \is_array($_POST['content'])) {
            $this->content = ArrayUtil::trim($_POST['content']);
        }
        if (isset($_POST['subject']) && \is_array($_POST['subject'])) {
            $this->subject = ArrayUtil::trim($_POST['subject']);
        }

        if (isset($_POST['acceptAddGroupIDs']) && \is_array($_POST['acceptAddGroupIDs'])) {
            $this->acceptAddGroupIDs = ArrayUtil::toIntegerArray($_POST['acceptAddGroupIDs']);
        }
        if (isset($_POST['acceptRemoveGroupIDs']) && \is_array($_POST['acceptRemoveGroupIDs'])) {
            $this->acceptRemoveGroupIDs = ArrayUtil::toIntegerArray($_POST['acceptRemoveGroupIDs']);
        }
        if (!empty($_POST['acceptUserAction'])) {
            $this->acceptUserAction = StringUtil::trim($_POST['acceptUserAction']);
        }
        if (!empty($_POST['acceptUrl'])) {
            $this->acceptUrl = StringUtil::trim($_POST['acceptUrl']);
        }
        if (isset($_POST['refuseAddGroupIDs']) && \is_array($_POST['refuseAddGroupIDs'])) {
            $this->refuseAddGroupIDs = ArrayUtil::toIntegerArray($_POST['refuseAddGroupIDs']);
        }
        if (isset($_POST['refuseRemoveGroupIDs']) && \is_array($_POST['refuseRemoveGroupIDs'])) {
            $this->refuseRemoveGroupIDs = ArrayUtil::toIntegerArray($_POST['refuseRemoveGroupIDs']);
        }
        if (!empty($_POST['refuseUserAction'])) {
            $this->refuseUserAction = StringUtil::trim($_POST['refuseUserAction']);
        }
        if (!empty($_POST['refuseUrl'])) {
            $this->refuseUrl = StringUtil::trim($_POST['refuseUrl']);
        }
        if (!empty($_POST['pages'])) {
            $this->pages = StringUtil::trim($_POST['pages']);
        }

        if ($this->hasPeriod && isset($_POST['periodStart'])) {
            $this->periodStart = $_POST['periodStart'];
            $this->periodStartObj = DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->periodStart);
        }
        if ($this->hasPeriod && isset($_POST['periodEnd'])) {
            $this->periodEnd = $_POST['periodEnd'];
            $this->periodEndObj = DateTime::createFromFormat('Y-m-d\TH:i:sP', $this->periodEnd);
        }

        // conditions
        foreach ($this->conditions as $conditions) {
            foreach ($conditions as $condition) {
                $condition->getProcessor()->readFormParameters();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

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
            'activationTime' => $this->isDisabled ? 0 : TIME_NOW,
            'addNewUser' => $this->addNewUser,
            'isDisabled' => $this->isDisabled,
            'isMultilingual' => $this->isMultilingual,
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

        $this->objectAction = new CompulsoryAction([], 'create', [
            'data' => \array_merge($this->additionalFields, $data),
            'content' => $content,
        ]);
        $returnValues = $this->objectAction->executeAction();

        $compulsoryEditor = new CompulsoryEditor($returnValues['returnValues']);
        $compulsoryID = $returnValues['returnValues']->compulsoryID;

        if (!I18nHandler::getInstance()->isPlainValue('title')) {
            I18nHandler::getInstance()->save(
                'title',
                'wcf.acp.compulsory.title' . $compulsoryID,
                'wcf.acp.compulsory',
                PackageCache::getInstance()->getPackageID('com.uz.wcf.compulsory')
            );
            $compulsoryEditor->update([
                'title' => 'wcf.acp.compulsory.title' . $compulsoryID,
            ]);
        }

        // transform conditions array into one-dimensional array
        $conditions = [];
        foreach ($this->conditions as $groupedObjectTypes) {
            $conditions = \array_merge($conditions, $groupedObjectTypes);
        }

        ConditionHandler::getInstance()->createConditions($returnValues['returnValues']->compulsoryID, $conditions);

        // Reset values
        $this->content = [];
        $this->activationTime = 0;
        $this->addNewUser = 1;
        $this->isDisabled = 1;
        $this->isRefusable = 0;
        $this->subject = [];
        $this->title = '';

        $this->acceptAddGroupIDs = [];
        $this->acceptRemoveGroupIDs = [];
        $this->acceptUserAction = 'none';
        $this->acceptUrl = '';
        $this->refuseAddGroupIDs = [];
        $this->refuseRemoveGroupIDs = [];
        $this->refuseUserAction = 'none';
        $this->refuseUrl = '';
        $this->pages = '';

        $this->hasPeriod = 0;
        $this->periodEnd = '';
        $this->periodStart = '';

        // reset language variables
        I18nHandler::getInstance()->reset();

        // reset conditions
        foreach ($this->conditions as $conditions) {
            foreach ($conditions as $condition) {
                $condition->getProcessor()->reset();
            }
        }

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();

        $this->saved();

        // Show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        // Title may not be empty and is multilingual, max 80 chars
        if (!I18nHandler::getInstance()->validateValue('title')) {
            if (I18nHandler::getInstance()->isPlainValue('title')) {
                throw new UserInputException('title');
            } else {
                throw new UserInputException('title', 'multilingual');
            }
        }
        if (\mb_strlen($this->title) > 80) {
            throw new UserInputException('title', 'tooLong');
        }

        if ($this->isMultilingual) {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                // subject
                if (empty($this->subject[$language->languageID])) {
                    throw new UserInputException('subject' . $language->languageID);
                }

                // content
                if (empty($this->content[$language->languageID])) {
                    throw new UserInputException('content' . $language->languageID);
                }

                $this->htmlInputProcessors[$language->languageID] = new HtmlInputProcessor();
                $this->htmlInputProcessors[$language->languageID]->process($this->content[$language->languageID], 'com.uz.wcf.compulsory.content', 0);
            }
        } else {
            // title
            if (empty($this->subject[0])) {
                throw new UserInputException('subject');
            }

            // content
            if (empty($this->content[0])) {
                throw new UserInputException('content');
            }

            $this->htmlInputProcessors[0] = new HtmlInputProcessor();
            $this->htmlInputProcessors[0]->process($this->content[0], 'com.uz.wcf.compulsory.content', 0);
        }

        // conditions
        foreach ($this->conditions as $conditions) {
            foreach ($conditions as $condition) {
                $condition->getProcessor()->validate();
            }
        }

        // period
        if ($this->hasPeriod) {
            $periodEnd = $periodStart = null;
            if (\strlen($this->periodStart)) {
                $periodStart = @\strtotime($this->periodStart);
                if ($periodStart === false) {
                    throw new UserInputException('period', 'invalidStart');
                }
            } else {
                throw new UserInputException('period', 'invalidStart');
            }
            if (\strlen($this->periodEnd)) {
                $periodEnd = @\strtotime($this->periodEnd);
                if ($periodEnd === false) {
                    throw new UserInputException('period', 'invalidEnd');
                }
            } else {
                throw new UserInputException('period', 'invalidEnd');
            }

            if ($periodEnd !== null && $periodStart !== null && $periodEnd < $periodStart) {
                throw new UserInputException('period', 'endBeforeStart');
            }

            if ($periodEnd < TIME_NOW) {
                throw new UserInputException('period', 'endInPast');
            }
        }

        // allowed groups
        $allowedGroupIDs = [];
        foreach ($this->groups as $group) {
            $allowedGroupIDs[] = $group->groupID;
        }

        if (\count($this->acceptAddGroupIDs)) {
            if (\count(\array_diff($this->acceptAddGroupIDs, $allowedGroupIDs))) {
                throw new UserInputException('acceptAddGroupIDs', 'invalidGroup');
            }
        }

        if (\count($this->acceptRemoveGroupIDs)) {
            if (\count(\array_diff($this->acceptRemoveGroupIDs, $allowedGroupIDs))) {
                throw new UserInputException('acceptRemoveGroupIDs', 'invalidGroup');
            }
        }

        if (\count($this->refuseAddGroupIDs)) {
            if (\count(\array_diff($this->refuseAddGroupIDs, $allowedGroupIDs))) {
                throw new UserInputException('refuseAddGroupIDs', 'invalidGroup');
            }
        }

        if (\count($this->refuseRemoveGroupIDs)) {
            if (\count(\array_diff($this->refuseRemoveGroupIDs, $allowedGroupIDs))) {
                throw new UserInputException('refuseRemoveGroupIDs', 'invalidGroup');
            }
        }
    }
}
