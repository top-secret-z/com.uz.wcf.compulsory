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
        $this->content = \str_replace('[username]', WCF::getUser()->username, $this->content);

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
            $sql = "SELECT    *
                    FROM    wcf" . WCF_N . "_compulsory_content
                    WHERE    compulsoryID = ? AND languageID = ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsoryID, $languageID]);
        } else {
            $sql = "SELECT    *
                    FROM    wcf" . WCF_N . "_compulsory_content
                    WHERE    compulsoryID = ? AND languageID IS NULL";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([$compulsoryID]);
        }

        if (($row = $statement->fetchSingleRow()) !== false) {
            return new self(null, $row);
        }

        return null;
    }
}
