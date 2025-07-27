<?php

namespace App\Domain\GameCalendar;

use App\Models\Console;

class AllowedDates
{
    const START_YEAR_SWITCH_1 = 2017;
    const START_YEAR_SWITCH_2 = 2025;

    public function getConsoleStartYear($consoleId)
    {
        if ($consoleId == Console::ID_SWITCH_2) {
            $startYear = self::START_YEAR_SWITCH_2;
        } else {
            $startYear = self::START_YEAR_SWITCH_1;
        }
        return $startYear;
    }

    public function getConsoleStartMonth($consoleId)
    {
        if ($consoleId == Console::ID_SWITCH_2) {
            $startMonth = 6; // June 2025
        } else {
            $startMonth = 3; // March 2017
        }
        return $startMonth;
    }

    /**
     * @deprecated
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

    public function releaseYearsByConsole($consoleId, $reverse = true)
    {
        $startYear = $this->getConsoleStartYear($consoleId);

        $releaseYears = [];
        for ($year = $startYear; $year <= date('Y'); $year++) {
            $releaseYears[] = $year;
        }

        if ($reverse) {
            $releaseYears = array_reverse($releaseYears);
        }

        return $releaseYears;
    }

    public function allowedDatesByConsole($consoleId, $reverse = true)
    {
        $allowedYears = $this->releaseYearsByConsole($consoleId);
        $startYear = $this->getConsoleStartYear($consoleId);
        $startMonth = $this->getConsoleStartMonth($consoleId);

        $dates = [];

        foreach ($allowedYears as $allowedYear) {

            for ($j=1; $j<13; $j++) {

                // Start from March 2017 (S1) or June 2025 (S2)
                if ($allowedYear == $startYear && $j < $startMonth) continue;
                // Don't go beyond the current month and year - Removed so we can have some Switch 2 ranks
                //if ($allowedYear == date('Y') && $j > date('m')+1) break;
                // Good to go
                $dateToAdd = $allowedYear.'-'.str_pad($j, 2, '0', STR_PAD_LEFT);
                $dates[] = $dateToAdd;

            }

        }

        if ($reverse) {
            $dates = array_reverse($dates);
        }

        return $dates;
    }

    public function allowedDatesByConsoleAndYear($consoleId, $year)
    {
        $years = [$year];

        $startYear = $this->getConsoleStartYear($consoleId);
        $startMonth = $this->getConsoleStartMonth($consoleId);

        if ($year < $startYear) return [];

        $dates = [];

        foreach ($years as $year) {

            for ($j=1; $j<13; $j++) {

                // Start from March 2017 (S1) or June 2025 (S2)
                if ($year == $startYear && $j < $startMonth) continue;
                // Don't go beyond the current month and year - Removed so we can have some Switch 2 ranks
                //if ($allowedYear == date('Y') && $j > date('m')+1) break;
                // Good to go
                $dateToAdd = $year.'-'.str_pad($j, 2, '0', STR_PAD_LEFT);
                $dates[] = $dateToAdd;

            }

        }

        return $dates;
    }

    /**
     * @deprecated
     * @param $reverse
     * @return array
     */
    public function allowedDates($reverse = true)
    {
        $allowedYears = $this->releaseYears();

        $dates = [];

        foreach ($allowedYears as $allowedYear) {

            for ($j=1; $j<13; $j++) {

                // Start from March 2017
                if ($allowedYear == 2017 && $j < 3) continue;
                // Don't go beyond the current month and year
                if ($allowedYear == date('Y') && $j > date('m')+1) break;
                // Good to go
                $dateToAdd = $allowedYear.'-'.str_pad($j, 2, '0', STR_PAD_LEFT);
                $dates[] = $dateToAdd;

            }

        }

        if ($reverse) {
            $dates = array_reverse($dates);
        }

        return $dates;
    }
}