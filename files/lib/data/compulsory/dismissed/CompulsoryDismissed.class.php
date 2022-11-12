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
namespace wcf\data\compulsory\dismissed;

use wcf\data\compulsory\Compulsory;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Represents a compulsory dismissed entry.
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
