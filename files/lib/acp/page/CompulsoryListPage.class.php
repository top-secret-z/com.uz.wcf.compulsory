<?php

namespace wcf\acp\page;

use wcf\data\compulsory\CompulsoryList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows the compulsory list page
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryListPage extends SortablePage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.compulsory.list';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_COMPULSORY'];

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageCompulsory'];

    /**
     * @inheritDoc
     */
    public $itemsPerPage = 15;

    /**
     * @inheritDoc
     */
    public $defaultSortField = 'compulsoryID';

    /**
     * @inheritDoc
     */
    public $validSortFields = ['compulsoryID', 'time', 'username', 'title', 'statAccept', 'statRefuse'];

    /**
     * @inheritDoc
     */
    public $objectListClassName = CompulsoryList::class;
}
