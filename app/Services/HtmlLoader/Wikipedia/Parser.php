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

    public function processTableData($tableData, $logger = null)
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
            $jpReleaseDate = $dateHandler->getReleaseDate($row[4], $rowErrorData);
            $usReleaseDate = $dateHandler->getReleaseDate($row[5], $rowErrorData);
            $euReleaseDate = $dateHandler->getReleaseDate($row[6], $rowErrorData);

            if ($logger) {
                //$logger->info('Processing item: '.$rowTitle.' || '.$row[4].'||'.$row[5].'||'.$row[6]);
            }

            \DB::insert('
                INSERT INTO crawler_wikipedia_games_list_source(title, genres, developers, publishers,
                release_date_eu, release_date_us, release_date_jp, created_at, updated_at)
                VALUES(?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ', [
                $rowTitle, $rowGenres, $rowDevs, $rowPubs,
                $euReleaseDate, $usReleaseDate, $jpReleaseDate
            ]);
        }
    }

}