<?php

namespace wcf\data\compulsory\content;

use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes compulsory content related actions.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryContentAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = CompulsoryContentEditor::class;
}
