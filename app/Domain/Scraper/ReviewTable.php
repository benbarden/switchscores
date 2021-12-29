<?php

namespace App\Domain\Scraper;

use App\Domain\Scraper\Base as BaseScraper;

class ReviewTable extends BaseScraper
{
    public function extractRows($tableId)
    {
        // Pocket Tactics
        $this->tableData = $this->domCrawler->filter('table#'.$tableId)->filter('tr')->each(function ($tr, $i) {

            return $tr->filter('td, th')->each(function ($td, $i) {

                $anchorCount = substr_count($td->html(), '<a ');
                if ($anchorCount == 1) {
                    $text = $td->filter('a')->text();
                    $url = $td->filter('a')->attr('href');
                    return [
                        'text' => $text,
                        'url' => $url
                    ];
                } else {
                    return trim($td->text());
                }

            });
        });
    }

    public function removeHeaderRow()
    {
        if (count($this->tableData) > 0) {
            array_shift($this->tableData);
        }
    }

}