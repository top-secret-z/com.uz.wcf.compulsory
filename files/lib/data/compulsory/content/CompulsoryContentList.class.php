<?php

namespace wcf\data\compulsory\content;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of compulsory contents.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryContentList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = CompulsoryContent::class;
}
