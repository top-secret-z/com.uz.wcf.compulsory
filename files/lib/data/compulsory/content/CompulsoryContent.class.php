<?php

namespace wcf\data\compulsory\content;

use wcf\data\compulsory\Compulsory;
use wcf\data\DatabaseObject;
use wcf\data\language\Language;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Represents a compulsory content.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryContent extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'compulsory_content';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'contentID';

    /**
     * compulsory object
     */
    protected $compulsory;

    /**
     * Returns the compulsory's formatted content.
     */
    public function getFormattedContent()
    {
        // replace placeholder username
        $this->content = str_replace('[username]', WCF::getUser()->username, $this->content);

        $processor = new HtmlOutputProcessor();
        if ($this->hasEmbeddedObjects) {
            MessageEmbeddedObjectManager::getInstance()->loadObjects('com.uz.wcf.compulsory.content', [$this->contentID]);
        }
        $processor->process($this->content, 'com.uz.wcf.compulsory.content', $this->contentID);

        return $processor->getHtml();
    }

    /**
     * Returns the language of this compulsory content or `null` if no language has been specified.
     */
    public function getLanguage()
    {
        if ($this->languageID) {
            return LanguageFactory::getInstance()->getLanguage($this->languageID);
        }

        return null;
    }

    /**
     * Returns a certain compulsory content or `null` if it does not exist.
     */
    public static function getCompulsoryContent($compulsoryID, $languageID)
    {
        if ($languageID !== null) {
            $sql = "SELECT	*
					FROM	wcf" . WCF_N . "_compulsory_content
					WHERE	compulsoryID = ? AND languageID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsoryID, $languageID]);
        } else {
            $sql = "SELECT	*
					FROM	wcf" . WCF_N . "_compulsory_content
					WHERE	compulsoryID = ? AND languageID IS NULL";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsoryID]);
        }

        if (($row = $statement->fetchSingleRow()) !== false) {
            return new CompulsoryContent(null, $row);
        }

        return null;
    }
}
