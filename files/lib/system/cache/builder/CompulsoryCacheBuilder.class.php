<?php

namespace wcf\system\cache\builder;

use wcf\data\compulsory\CompulsoryList;
use wcf\system\cache\builder\AbstractCacheBuilder;

/**
 * Caches the active compulsories.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 300;

    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $compulsoryList = new CompulsoryList();
        $compulsoryList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $compulsoryList->sqlOrderBy = 'time ASC';
        $compulsoryList->readObjects();

        return $compulsoryList->getObjects();
    }
}
