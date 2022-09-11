<?php

namespace wcf\data\compulsory;

use wcf\data\compulsory\content\CompulsoryContent;
use wcf\data\compulsory\content\CompulsoryContentEditor;
use wcf\data\compulsory\content\CompulsoryContentList;
use wcf\data\condition\ConditionList;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\IToggleAction;
use wcf\system\cache\builder\CompulsoryCacheBuilder;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Executes compulsory-related actions.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    /**
     * @inheritDoc
     */
    protected $className = CompulsoryEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.user.canManageCompulsory'];
    protected $permissionsDelete = ['admin.user.canManageCompulsory'];
    protected $permissionsUpdate = ['admin.user.canManageCompulsory'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'toggle', 'update'];

    public $compulsory = null;

    /**
     * @inheritDoc
     */
    public function delete()
    {
        ConditionHandler::getInstance()->deleteConditions('com.uz.wcf.compulsory.condition', $this->objectIDs);

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();

        return parent::delete();
    }

    /**
     * @inheritDoc
     */
    public function validateToggle()
    {
        parent::validateUpdate();
    }

    /**
     * @inheritDoc
     */
    public function toggle()
    {
        foreach ($this->objects as $compulsory) {
            if ($compulsory->isDisabled && !$compulsory->activationTime) {
                $compulsory->activationTime = TIME_NOW;
            }
            $compulsory->update([
                                    'isDisabled' => $compulsory->isDisabled ? 0 : 1,
                                    'activationTime' => $compulsory->activationTime
                                ]);
        }

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();
    }

    /**
     * @inheritDoc
     */
    public function create()
    {
        $compulsory = parent::create();

        // save compulsory content
        if (!empty($this->parameters['content'])) {
            foreach ($this->parameters['content'] as $languageID => $content) {
                if (!empty($content['htmlInputProcessor'])) {
                    $content['content'] = $content['htmlInputProcessor']->getHtml();
                }

                $compulsoryContent = CompulsoryContentEditor::create([
                                                                         'compulsoryID' => $compulsory->compulsoryID,
                                                                         'languageID' => $languageID ?: null,
                                                                         'subject' => $content['subject'],
                                                                         'content' => $content['content']
                                                                     ]);
                $compulsoryContentEditor = new CompulsoryContentEditor($compulsoryContent);

                // save embedded objects
                if (!empty($content['htmlInputProcessor'])) {
                    $content['htmlInputProcessor']->setObjectID($compulsoryContent->contentID);
                    if (MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
                        $compulsoryContentEditor->update(['hasEmbeddedObjects' => 1]);
                    }
                }
            }
        }

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();

        return $compulsory;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        // update compulsory content
        if (!empty($this->parameters['content'])) {
            foreach ($this->getObjects() as $compulsory) {
                foreach ($this->parameters['content'] as $languageID => $content) {
                    if (!empty($content['htmlInputProcessor'])) {
                        $content['content'] = $content['htmlInputProcessor']->getHtml();
                    }

                    $compulsoryContent = CompulsoryContent::getCompulsoryContent($compulsory->compulsoryID, ($languageID ?: null));
                    $compulsoryContentEditor = null;
                    if ($compulsoryContent !== null) {
                        // update
                        $compulsoryContentEditor = new CompulsoryContentEditor($compulsoryContent);
                        $compulsoryContentEditor->update([
                                                             'content' => $content['content'],
                                                             'subject' => $content['subject']
                                                         ]);
                    } else {
                        /** @var CompulsoryContent $compulsoryContent */
                        $compulsoryContent = CompulsoryContentEditor::create([
                                                                                 'compulsoryID' => $compulsory->compulsoryID,
                                                                                 'languageID' => $languageID ?: null,
                                                                                 'content' => $content['content'],
                                                                                 'subject' => $content['subject']
                                                                             ]);
                        $compulsoryContentEditor = new CompulsoryContentEditor($compulsoryContent);
                    }

                    // save embedded objects
                    if (!empty($content['htmlInputProcessor'])) {
                        $content['htmlInputProcessor']->setObjectID($compulsoryContent->contentID);
                        if ($compulsoryContent->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
                            $compulsoryContentEditor->update(['hasEmbeddedObjects' => $compulsoryContent->hasEmbeddedObjects ? 0 : 1]);
                        }
                    }
                }
            }
        }

        // reset cache
        CompulsoryCacheBuilder::getInstance()->reset();
    }

    /**
     * Validates the copy action.
     */
    public function validateCopy()
    {
        $this->compulsory = new Compulsory($this->parameters['objectID']);
        if (!$this->compulsory->compulsoryID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * Executes the copy action.
     */
    public function copy()
    {
        $data = $this->compulsory->getData();
        $oldCompulsoryID = $data['compulsoryID'];
        unset($data['compulsoryID']);

        // copy compulsory, set to disable / 0
        $data['isDisabled'] = 1;
        $data['activationTime'] = 0;
        $data['statAccept'] = 0;
        $data['statRefuse'] = 0;
        $data['title'] = substr($data['title'], 0, 250) . ' (2)';

        $this->parameters['data'] = $data;
        $compulsory = $this->create();

        // copy conditions
        $definitionIDs = [];
        $sql = "SELECT		definitionID
				FROM		wcf" . WCF_N . "_object_type_definition
				WHERE		definitionName LIKE ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute(['com.uz.wcf.compulsory.condition']);
        while ($row = $statement->fetchArray()) {
            $definitionIDs[] = $row['definitionID'];
        }

        foreach ($definitionIDs as $definitionID) {
            $objectTypeIDs = [];
            $sql = "SELECT		objectTypeID
					FROM		wcf" . WCF_N . "_object_type
					WHERE		definitionID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$definitionID]);
            while ($row = $statement->fetchArray()) {
                $objectTypeIDs[] = $row['objectTypeID'];
            }

            $conditionList = new ConditionList();
            $conditionList->getConditionBuilder()->add('objectTypeID IN (?)', [$objectTypeIDs]);
            $conditionList->getConditionBuilder()->add('objectID = ?', [$oldCompulsoryID]);
            $conditionList->readObjects();
            $conditions = $conditionList->getObjects();

            if (count($conditions)) {
                WCF::getDB()->beginTransaction();
                $sql = "INSERT INTO wcf" . WCF_N . "_condition
								(objectID, objectTypeID, conditionData)
						VALUES	(?, ?, ?)";
                $statement = WCF::getDB()->prepareStatement($sql);

                foreach ($conditions as $condition) {
                    $statement->execute([$compulsory->compulsoryID, $condition->objectTypeID, serialize($condition->conditionData)]);
                }
                WCF::getDB()->commitTransaction();
            }
        }

        ConditionCacheBuilder::getInstance()->reset();

        // copy content
        $contentList = new CompulsoryContentList();
        $contentList->getConditionBuilder()->add('compulsoryID = ?', [$oldCompulsoryID]);
        $contentList->readObjects();
        $contents = $contentList->getObjects();

        WCF::getDB()->beginTransaction();
        $sql = "INSERT INTO wcf" . WCF_N . "_compulsory_content
							(compulsoryID, languageID, content, subject, hasEmbeddedObjects)
				VALUES	(?, ?, ?, ?, ?)";
        $statement = WCF::getDB()->prepareStatement($sql);

        foreach ($contents as $content) {
            $statement->execute([$compulsory->compulsoryID, $content->languageID, $content->content, $content->subject, $content->hasEmbeddedObjects]);
        }
        WCF::getDB()->commitTransaction();

        return [
            'redirectURL' => LinkHandler::getInstance()->getLink('CompulsoryEdit', ['id' => $compulsory->compulsoryID])
        ];
    }
}
