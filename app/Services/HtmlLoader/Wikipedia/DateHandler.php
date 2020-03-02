<?php


namespace App\Services\HtmlLoader\Wikipedia;


class DateHandler
{
    public function getYearsArray()
    {
        $dtNow = new \DateTime('now');
        $yearsArray = [];
        // Go up to 2 years in the future
        for ($year = 2017; $year <= ((int)$dtNow->format('Y') + 2); $year++) {
            // cast to string
            $yearsArray[] = ''.$year.'';
        }

        return $yearsArray;
    }

    public function getYearMonthsArray()
    {
        $yearMonthsArray = [];

        $monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        $invalidOptions = ['January 2017', 'February 2017'];

        $yearsArray = $this->getYearsArray();

        foreach ($yearsArray as $year) {
            foreach ($monthNames as $month) {
                $yearMonth = $month.' '.$year;
                if (in_array($yearMonth, $invalidOptions)) continue;
                $yearMonthsArray[] = $yearMonth;
            }
        }

        return $yearMonthsArray;
    }

    public function getYearQuartersArray()
    {
        $yearQuartersArray = [];

        $quarterNames = ['Q1', 'Q2', 'Q3', 'Q4'];

        $yearsArray = $this->getYearsArray();

        foreach ($yearsArray as $year) {
            foreach ($quarterNames as $quarter) {
                $yearQuartersArray[] = $quarter.' '.$year;
            }
        }

        return $yearQuartersArray;
    }

    public function getUpcomingDate($releaseDateRaw)
    {
        if ($this->isYear($releaseDateRaw)) {

            $upcomingDate = $releaseDateRaw.'-XX';
            return $upcomingDate;

        }

        if ($this->isYearXX($releaseDateRaw)) {

            $upcomingDate = $releaseDateRaw;
            return $upcomingDate;

        }

        if ($this->isQuarterYear($releaseDateRaw)) {

            $dateLogic = explode(' ', $releaseDateRaw);
            $upcomingDate = $dateLogic[1].'-'.$dateLogic[0];
            return $upcomingDate;

        }

        if ($this->isQuarterYearXX($releaseDateRaw)) {

            $upcomingDate = $releaseDateRaw;
            return $upcomingDate;

        }

        if ($this->isMonthYear($releaseDateRaw)) {

            $dtMonthYear = new \DateTime($releaseDateRaw);
            $upcomingDate = $dtMonthYear->format('Y-m').'-XX';
            return $upcomingDate;

        }

        if ($this->isUnreleasedOrTBA($releaseDateRaw)) {

            $upcomingDate = $releaseDateRaw;
            return $upcomingDate;

        }

        // Special cases for junk dates we can't use
        switch ($releaseDateRaw) {

            case 'Fall 2019':
                return '2019-Q3';
                break;

        }

        // Should be a real date - ok to continue
        $upcomingDate = null;

        return $upcomingDate;
    }

    public function isYear($releaseDateRaw)
    {
        // 2018, 2019
        $yearsArray = $this->getYearsArray();
        return in_array($releaseDateRaw, $yearsArray, true);
    }

    /**
     * @param $releaseDateRaw
     * @return bool
     */
    public function isYearXX($releaseDateRaw)
    {
        $foundMatch = false;
        $yearsArray = $this->getYearsArray();
        foreach ($yearsArray as $year) {
            if ($releaseDateRaw == $year.'-XX') {
                $foundMatch = true;
                break;
            }
        }
        return $foundMatch;
    }

    public function isQuarterYear($releaseDateRaw)
    {
        // Quarter, Year
        $yearQuartersArray = $this->getYearQuartersArray();
        return in_array($releaseDateRaw, $yearQuartersArray, true);
    }

    /**
     * @param $releaseDateRaw
     * @return bool
     */
    public function isQuarterYearXX($releaseDateRaw)
    {
        $foundMatch = false;
        $yearQuartersArray = $this->getYearQuartersArray();
        foreach ($yearQuartersArray as $yearQuarter) {
            $yqArray = explode(' ', $yearQuarter);
            if ($releaseDateRaw == $yqArray[1].'-'.$yqArray[0]) {
                $foundMatch = true;
                break;
            }
        }
        return $foundMatch;
    }

    public function isMonthYear($releaseDateRaw)
    {
        // Month, Year
        $yearMonthsArray = $this->getYearMonthsArray();
        return in_array($releaseDateRaw, $yearMonthsArray, true);
    }

    public function isUnreleasedOrTBA($releaseDateRaw)
    {
        // Unreleased, TBA
        return in_array($releaseDateRaw, ['Unreleased', 'TBA'], true);
    }

    public function getReleaseDate($row, $rowData = "")
    {
        $releaseDate = null;

        if (is_array($row)) {
            if (count($row) == 2) {
                $releaseDateRaw = $row[1];
            } else {
                //$this->warn('Unexpected array count for game '.$title);
                $releaseDateRaw = $row;
            }
        } else {
            $releaseDateRaw = $row;
        }

        // Is this a real date?
        $upcomingDate = $this->getUpcomingDate($releaseDateRaw);
        $okToParseDate = $upcomingDate == null ? true : false;

        if ($okToParseDate) {

            try {
                $dtReleaseDate = new \DateTime($releaseDateRaw);
                $releaseDate = $dtReleaseDate->format('Y-m-d');
            } catch (\Exception $e) {
                throw new \Exception($rowData.' - Failed to parse date: '.$releaseDateRaw);
            }

        } else {

            $releaseDate = null;

        }

        return $releaseDate;
    }

}