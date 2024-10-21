<?php

namespace App\Domain\Scraper;

use App\Domain\Scraper\Base as BaseScraper;

class NintendoCoUkSearchResults extends BaseScraper
{
    public function getResults()
    {
        $results = $this->domCrawler->filterXPath('//ul[@class="results"]')->html();
        return $results;
    }
}