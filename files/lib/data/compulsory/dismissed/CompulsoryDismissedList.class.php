<?php

namespace wcf\data\compulsory\dismissed;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of compulsory dismissed entries.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryDismissedList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = CompulsoryDismissed::class;
}
