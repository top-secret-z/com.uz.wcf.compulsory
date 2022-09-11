<?php

namespace wcf\data\compulsory\content;

use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit compulsory content.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryContentEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = CompulsoryContent::class;
}
