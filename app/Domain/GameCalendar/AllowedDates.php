<?php

namespace App\Domain\GameCalendar;

class AllowedDates
{
    /**
     * @return int[]
     */
    public function getAllowedYears()
    {
        $releaseYears = [];
        for ($year = 2017; $year <= date('Y'); $year++) {
            $releaseYears[] = $year;
        }
        return $releaseYears;
    }
}