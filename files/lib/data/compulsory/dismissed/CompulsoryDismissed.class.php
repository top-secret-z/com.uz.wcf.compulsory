<?php

namespace wcf\data\compulsory\dismissed;

use wcf\data\compulsory\Compulsory;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Represents a compulsory dismissed entry.
 *
 * @author        2016-2022 Darkwood.Design
 * @license        Commercial Darkwood.Design License <https://darkwood.design/lizenz/>
 * @package        com.uz.wcf.compulsory
 */
class CompulsoryDismissed extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableName = 'compulsory_dismissed';

    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'dismissedID';

    /**
     * list of point of times for each period's end
     */
    protected static $periods = [];

    /**
     * Returns the readable period matching this dismiss.
     */
    public function getPeriod()
    {
        if (empty(self::$periods)) {
            $date = DateUtil::getDateTimeByTimestamp(TIME_NOW);
            $date->setTimezone(WCF::getUser()->getTimeZone());
            $date->setTime(0, 0, 0);

            self::$periods[$date->getTimestamp()] = WCF::getLanguage()->get('wcf.date.period.today');

            // 1 day back
            $date->modify('-1 day');
            self::$periods[$date->getTimestamp()] = WCF::getLanguage()->get('wcf.date.period.yesterday');

            // 2-6 days back
            for ($i = 0; $i < 6; $i++) {
                $date->modify('-1 day');
                self::$periods[$date->getTimestamp()] = DateUtil::format($date, 'l');
            }
        }

        foreach (self::$periods as $time => $period) {
            if ($this->time >= $time) {
                return $period;
            }
        }

        return WCF::getLanguage()->get('wcf.date.period.older');
    }
}
