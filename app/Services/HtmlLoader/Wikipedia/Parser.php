<?php


namespace App\Services\HtmlLoader\Wikipedia;


class Parser
{
    public function getDates($row, $title = "")
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

        if (in_array($releaseDateRaw, ['2018', '2019'])) {
            // 2018, 2019
            $upcomingDate = $releaseDateRaw.'-XX';
        } elseif (in_array($releaseDateRaw, [
                'Q2 2018', 'Q3 2018', 'Q4 2018',
                'Q1 2019', 'Q2 2019', 'Q3 2019', 'Q4 2019'
            ])) {
            // Quarter, Year
            $dateLogic = explode(' ', $releaseDateRaw);
            $upcomingDate = $dateLogic[1].'-'.$dateLogic[0];
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
                throw new \Exception('Failed to parse date: '.$releaseDateRaw);
            }

            $upcomingDate = $releaseDate;
            if ($upcomingDate > $dtNow) {
                $isReleased = 1;
            } else {
                $isReleased = 0;
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
            list($jpReleaseDate, $jpUpcomingDate, $jpIsReleased) = $this->getDates($row[4], $rowTitle);
            list($usReleaseDate, $usUpcomingDate, $usIsReleased) = $this->getDates($row[5], $rowTitle);
            list($euReleaseDate, $euUpcomingDate, $euIsReleased) = $this->getDates($row[6], $rowTitle);

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