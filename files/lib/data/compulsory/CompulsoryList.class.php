<?php

namespace wcf\data\compulsory;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of compulsories.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = Compulsory::class;
}
