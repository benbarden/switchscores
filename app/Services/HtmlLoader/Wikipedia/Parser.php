<?php


namespace App\Services\HtmlLoader\Wikipedia;


class Parser
{
    public function limitField($input, $maxChars)
    {
        if (strlen($input) > $maxChars) {
            $output = substr($input, 0, $maxChars);
        } else {
            $output = $input;
        }
        return $output;
    }

    public function flattenArray($input)
    {
        if (is_array($input)) {
            $output = implode(', ', $input);
        } else {
            $output = $input;
        }
        return $output;
    }

    public function processTableData($tableData)
    {
        $counter = -1;

        $dateHandler = new DateHandler();

        foreach ($tableData as $row) {

            $counter++;

            // Skip the first two rows
            if (in_array($counter, [0, 1])) continue;

            $rowTitle = $this->limitField($row[0], 150);
            $rowGenres = $this->limitField($row[1], 150);
            $rowDevs = $this->flattenArray($row[2]);
            $rowPubs = $this->flattenArray($row[3]);

            // Release dates
            $rowErrorData = $rowTitle.','.$rowDevs.','.$rowPubs; // used if the date fails
            list($jpReleaseDate, $jpUpcomingDate, $jpIsReleased) = $dateHandler->getDates($row[4], $rowErrorData);
            list($usReleaseDate, $usUpcomingDate, $usIsReleased) = $dateHandler->getDates($row[5], $rowErrorData);
            list($euReleaseDate, $euUpcomingDate, $euIsReleased) = $dateHandler->getDates($row[6], $rowErrorData);

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