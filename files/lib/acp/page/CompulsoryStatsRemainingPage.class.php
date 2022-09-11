<?php

namespace wcf\acp\page;

use wcf\data\compulsory\Compulsory;
use wcf\data\user\User;
use wcf\data\user\UserProfileList;
use wcf\page\SortablePage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the compulsory stats page
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryStatsRemainingPage extends SortablePage
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
            $id = intval($_REQUEST['id']);
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

        $conditions = $this->compulsory->getConditions();
        foreach ($conditions as $condition) {
            $condition->getObjectType()->getProcessor()->addUserCondition($condition, $this->objectList);
        }

        $this->objectList->getConditionBuilder()->add(
            'user_table.userID NOT IN (SELECT userID FROM wcf' . WCF_N . '_compulsory_dismissed WHERE userID IS NOT NULL AND compulsoryID = ?)',
            [$this->compulsory->compulsoryID]
        );

        // include new users
        if (!$this->compulsory->addNewUser) {
            $this->objectList->getConditionBuilder()->add('user_table.registrationDate < ?', [$this->compulsory->activationTime]);
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
                                  'compulsory' => $this->compulsory
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
