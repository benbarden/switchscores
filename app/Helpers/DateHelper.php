<?php


namespace App\Helpers;


class DateHelper
{
    static function isNew($releaseDate)
    {
        if (date('Y-m-d', strtotime('-7 days')) < $releaseDate) {
            return true;
        } else {
            return false;
        }
    }

    static function firstDayOfWeek($year, $week)
    {
        // we need to specify 'today' otherwise datetime constructor uses 'now' which includes current time
        $today = new \DateTime('today');

        $firstDay = clone $today->setISODate($year, $week, 0);
        return $firstDay;
        /*
        return (object)[
            'first_day' => clone $today->setISODate($year, $week, 0),
            'last_day' => clone $today->setISODate($year, $week, 6)
        ];
        */
    }
}