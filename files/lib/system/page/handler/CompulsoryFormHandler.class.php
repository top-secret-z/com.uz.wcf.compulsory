<?php

namespace wcf\system\page\handler;

use wcf\system\WCF;

/**
 * Menu page handler for the compulsory form.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryFormHandler extends AbstractMenuPageHandler
{
    /**
     * @inheritDoc
     */
    public function isVisible($objectID = null)
    {
        // always hide
        return false;
    }
}
