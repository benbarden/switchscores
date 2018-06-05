<?php


namespace App\Services\HtmlLoader\Wikipedia;


class Parser
{
    public function getYearsArray()
    {
        $dtNow = new \DateTime('now');
        $yearsArray = [];
        // Go up to 2 years in the future
        for ($year = 2017; $year <= ((int)$dtNow->format('Y') + 2); $year++) {
            $yearsArray[] = $year;
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

    public function getDates($row, $rowData = "")
    {
        $releaseDate = null;
        $upcomingDate = null;
        $isReleased = null;

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

        $dtNow = new \DateTime('now');

        $okToParseDate = false;

        // Dynamic date arrays
        $yearsArray = $this->getYearsArray();
        $yearMonthsArray = $this->getYearMonthsArray();
        $yearQuartersArray = $this->getYearQuartersArray();

        if (in_array($releaseDateRaw, $yearsArray)) {
            // 2018, 2019
            $upcomingDate = $releaseDateRaw . '-XX';
        } elseif (in_array($releaseDateRaw, $yearQuartersArray)) {
            // Quarter, Year
            $dateLogic = explode(' ', $releaseDateRaw);
            $upcomingDate = $dateLogic[1].'-'.$dateLogic[0];
        } elseif (in_array($releaseDateRaw, $yearMonthsArray)) {
            // Month, Year
            $dtMonthYear = new \DateTime($releaseDateRaw);
            $upcomingDate = $dtMonthYear->format('Y-m').'-XX';
        } elseif (in_array($releaseDateRaw, ['Unreleased', 'TBA'])) {
            // Unreleased, TBA
            $upcomingDate = $releaseDateRaw;
        } else {
            // Should be a real date - ok to continue
            $okToParseDate = true;
        }

        if ($okToParseDate) {

            try {
                $dtReleaseDate = new \DateTime($releaseDateRaw);
                $releaseDate = $dtReleaseDate->format('Y-m-d');
            } catch (\Exception $e) {
                throw new \Exception($rowData.' - Failed to parse date: '.$releaseDateRaw);
            }

            $upcomingDate = $releaseDate;

            if ($dtReleaseDate > $dtNow) {
                $isReleased = 0;
            } else {
                $isReleased = 1;
            }

        } else {

            $releaseDate = null;
            $isReleased = 0;

        }

        return [$releaseDate, $upcomingDate, $isReleased];
    }

    public function processTableData($tableData)
    {
        $counter = -1;

        foreach ($tableData as $row) {

            $counter++;

            // Skip the first two rows
            if (in_array($counter, [0, 1])) continue;

            // Handle fields
            if (strlen($row[0]) > 150) {
                $rowTitle = substr($row[0], 0, 150);
            } else {
                $rowTitle = $row[0];
            }

            if (strlen($row[1]) > 150) {
                $rowGenres = substr($row[1], 0, 150);
            } else {
                $rowGenres = $row[1];
            }

            $rowDevs = $row[2];
            $rowPubs = $row[3];

            // Release dates
            $rowErrorData = $rowTitle.','.$rowDevs.','.$rowPubs; // used if the date fails
            list($jpReleaseDate, $jpUpcomingDate, $jpIsReleased) = $this->getDates($row[4], $rowErrorData);
            list($usReleaseDate, $usUpcomingDate, $usIsReleased) = $this->getDates($row[5], $rowErrorData);
            list($euReleaseDate, $euUpcomingDate, $euIsReleased) = $this->getDates($row[6], $rowErrorData);

            //$this->info('Processing item: '.$rowTitle);

            \DB::insert('
                INSERT INTO crawler_wikipedia_games_list_source(title, genres, developers, publishers,
                release_date_eu, upcoming_date_eu, is_released_eu,
                release_date_us, upcoming_date_us, is_released_us,
                release_date_jp, upcoming_date_jp, is_released_jp,
                created_at, updated_at)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $rowTitle, $rowGenres, $rowDevs, $rowPubs,
                $euReleaseDate, $euUpcomingDate, $euIsReleased,
                $usReleaseDate, $usUpcomingDate, $usIsReleased,
                $jpReleaseDate, $jpUpcomingDate, $jpIsReleased
            ]);
        }
    }

}