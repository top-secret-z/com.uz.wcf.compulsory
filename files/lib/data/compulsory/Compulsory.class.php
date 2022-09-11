<?php

namespace wcf\data\compulsory;

use wcf\data\compulsory\CompulsoryList;
use wcf\data\compulsory\content\CompulsoryContent;
use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\user\UserList;
use wcf\system\condition\ConditionHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\IRouteController;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a Compulsory
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class Compulsory extends DatabaseObject implements IRouteController
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'compulsory';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'compulsoryID';

    /**
     * list of compulsoryIDs
     */
    protected $compulsoryIDs = null;

    /**
     * compulsory content grouped by language id
     */
    public $compulsoryContents;

    /**
     * true if the active user has dismissed the compulsory
     */
    protected $isDismissed = null;

    /**
     * @inheritDoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the active content version.
     */
    public function getCompulsoryContent()
    {
        $this->getCompulsoryContents();

        if ($this->isMultilingual) {
            if (isset($this->compulsoryContents[WCF::getLanguage()->languageID])) {
                return $this->compulsoryContents[WCF::getLanguage()->languageID];
            } else {
                // get content for default language
                return $this->compulsoryContents[LanguageFactory::getInstance()->getDefaultLanguageID()];
            }
        } else {
            if (!empty($this->compulsoryContents[0])) {
                return $this->compulsoryContents[0];
            }
        }

        return null;
    }

    /**
     * Returns the compulsory's content.
     */
    public function getCompulsoryContents()
    {
        if ($this->compulsoryContents === null) {
            $this->compulsoryContents = [];

            $sql = "SELECT	*
					FROM	wcf" . WCF_N . "_compulsory_content
					WHERE	compulsoryID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$this->compulsoryID]);
            while ($row = $statement->fetchArray()) {
                $this->compulsoryContents[$row['languageID'] ?: 0] = new CompulsoryContent(null, $row);
            }
        }

        return $this->compulsoryContents;
    }

    /**
     * Returns the active content string.
     */
    public function getCompulsoryString($length = 255)
    {
        $this->getCompulsoryContents();

        $string = '';
        if ($this->isMultilingual) {
            if (isset($this->compulsoryContents[WCF::getLanguage()->languageID])) {
                $string = $this->compulsoryContents[WCF::getLanguage()->languageID]->content;
            } else {
                // get content for default language
                $string = $this->compulsoryContents[LanguageFactory::getInstance()->getDefaultLanguageID()]->content;
            }
        } else {
            if (!empty($this->compulsoryContents[0])) {
                $string = $this->compulsoryContents[0]->content;
            }
        }
        return StringUtil::truncateHTML(StringUtil::stripHTML($string), $length);
    }

    /**
     * Returns the conditions of the compulsory.
     */
    public function getConditions()
    {
        return ConditionHandler::getInstance()->getConditions('com.uz.wcf.compulsory.condition', $this->compulsoryID);
    }

    /**
     * Returns true if the active user has dismissed the compulsory
     * and configures dismissedCompulsories if not set
     */
    public function isDismissed()
    {
        if ($this->isDismissed === null) {
            if (WCF::getUser()->userID) {
                $user = WCF::getUser();

                // check user storage for already dismissed compulsories
                $dismissedCompulsories = UserStorageHandler::getInstance()->getField('dismissedCompulsories');

                if ($dismissedCompulsories === null) {
                    $compulsoryIDs = [];

                    // get active dismisses
                    $sql = "SELECT	compulsoryID
							FROM	wcf" . WCF_N . "_compulsory_dismissed
							WHERE	userID = ?";
                    $statement = WCF::getDB()->prepareStatement($sql);
                    $statement->execute([WCF::getUser()->userID]);
                    while ($compulsoryID = $statement->fetchColumn()) {
                        $compulsoryIDs[] = $compulsoryID;
                    }

                    // dismiss compulsories not related to user - addNewUser
                    $compulsoryList = new CompulsoryList();
                    $compulsoryList->getConditionBuilder()->add('addNewUser = ?', [0]);
                    $compulsoryList->readObjects();
                    $compulsories = $compulsoryList->getObjects();

                    if (count($compulsories)) {
                        foreach ($compulsories as $compulsory) {
                            // before user registration?
                            if ($compulsory->time < $user->registrationDate) {
                                $compulsoryIDs[] = $compulsory->compulsoryID;
                                continue;
                            }

                            // dismiss if conditions are not met
                            $dismiss = false;
                            $conditions = $compulsory->getConditions();
                            foreach ($conditions as $condition) {
                                if (!$condition->getObjectType()->getProcessor()->checkUser($condition, $user)) {
                                    $dismiss = true;
                                    break;
                                }
                            }
                            if ($dismiss) {
                                $compulsoryIDs[] = $compulsory->compulsoryID;
                            }
                        }
                    }

                    // dismiss found compulsories and report status of this
                    if (count($compulsoryIDs)) {
                        $compulsoryIDs = array_unique($compulsoryIDs);
                        UserStorageHandler::getInstance()->update(WCF::getUser()->userID, 'dismissedCompulsories', serialize($compulsoryIDs));
                        if (in_array($this->compulsoryID, $compulsoryIDs)) {
                            $this->isDismissed = true;
                        }
                    }
                } else {
                    // return immediately if already dismissed
                    $dismissedCompulsoryIDs = @unserialize($dismissedCompulsories);
                    if (in_array($this->compulsoryID, $dismissedCompulsoryIDs)) {
                        return true;
                    }

                    // check !addNewUser
                    if (!$this->addNewUser) {
                        $dismiss = false;
                        if ($this->time < $user->registrationDate) {
                            $dismiss = true;
                        }

                        if (!$dismiss) {
                            $conditions = $this->getConditions();
                            foreach ($conditions as $condition) {
                                if (!$condition->getObjectType()->getProcessor()->checkUser($condition, $user)) {
                                    $dismiss = true;
                                    break;
                                }
                            }
                        }
                        if ($dismiss) {
                            $dismissedCompulsoryIDs[] = $this->compulsoryID;
                            UserStorageHandler::getInstance()->update($user->userID, 'dismissedCompulsories', serialize($dismissedCompulsoryIDs));
                            $this->isDismissed = true;
                        }
                    }
                }
            }
        }

        return $this->isDismissed;
    }

    /**
     * Returns the number of users who accepted
     */
    public function getNumberAccepted()
    {
        $sql = "SELECT	COUNT(*) AS count
				FROM	wcf" . WCF_N . "_compulsory_dismissed
				WHERE	compulsoryID = ? AND choice = ? AND userID IS NOT NULL";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->compulsoryID, 'accept']);
        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the number of deleted users who accepted
     */
    public function getNumberAcceptedDeleted()
    {
        $sql = "SELECT	COUNT(*) AS count
				FROM	wcf" . WCF_N . "_compulsory_dismissed
				WHERE	compulsoryID = ? AND choice = ? AND userID IS NULL";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->compulsoryID, 'accept']);
        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the number of users who refused
     */
    public function getNumberRefused()
    {
        $sql = "SELECT	COUNT(*) AS count
				FROM	wcf" . WCF_N . "_compulsory_dismissed
				WHERE	compulsoryID = ? AND choice = ? AND userID IS NOT NULL";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->compulsoryID, 'refuse']);
        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the number of deleted users who refused
     */
    public function getNumberRefusedDeleted()
    {
        $sql = "SELECT	COUNT(*) AS count
				FROM	wcf" . WCF_N . "_compulsory_dismissed
				WHERE	compulsoryID = ? AND choice = ? AND userID IS NULL";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([$this->compulsoryID, 'refuse']);
        return $statement->fetchSingleColumn();
    }

    /**
     * Returns the number of remaining users
     */
    public function getNumberRemaining()
    {
        $userList = new UserList();

        // conditions
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            $condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
        }

        // include new users
        if (!$this->addNewUser) {
            $userList->getConditionBuilder()->add('user_table.registrationDate < ?', [$this->activationTime]);
        }

        // accepted / refused
        $userList->getConditionBuilder()->add(
            'user_table.userID NOT IN (SELECT userID FROM wcf' . WCF_N . '_compulsory_dismissed WHERE userID IS NOT NULL AND compulsoryID = ?)',
            [$this->compulsoryID]
        );

        return $userList->countObjects();
    }

    /**
     * Returns the number of affected users
     */
    public function getNumberTotal()
    {
        $userList = new UserList();

        // conditions
        $conditions = $this->getConditions();
        foreach ($conditions as $condition) {
            $condition->getObjectType()->getProcessor()->addUserCondition($condition, $userList);
        }

        // include new users
        if (!$this->addNewUser) {
            $userList->getConditionBuilder()->add('user_table.registrationDate < ?', [$this->activationTime]);
        }
        return $userList->countObjects();
    }
}
