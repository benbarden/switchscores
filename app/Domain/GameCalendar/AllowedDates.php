<?php

namespace App\Domain\GameCalendar;

class AllowedDates
{
    /**
     * @return int[]
     */
    public function releaseYears($reverse = true)
    {
        $releaseYears = [];
        for ($year = 2017; $year <= date('Y'); $year++) {
            $releaseYears[] = $year;
        }

        if ($reverse) {
            $releaseYears = array_reverse($releaseYears);
        }

        return $releaseYears;
    }
}