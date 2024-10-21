<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Domain\Scraper\NintendoCoUkSearchResults;

class ImportSearchPage
{
    const SEARCH_RECENTLY_RELEASED = 'https://www.nintendo.com/en-gb/Search/Search-299117.html?f=147394-16-74';

    private $scraper;

    public function __construct()
    {
        $this->scraper = new NintendoCoUkSearchResults();
    }

    public function downloadHtml()
    {
        $this->scraper->crawlPage(self::SEARCH_RECENTLY_RELEASED);
        $html = $this->scraper->getResults();
        return $html;
    }

}