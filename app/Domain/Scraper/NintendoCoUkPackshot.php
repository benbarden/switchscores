<?php

namespace App\Domain\Scraper;

use App\Domain\Scraper\Base as BaseScraper;

class NintendoCoUkPackshot extends BaseScraper
{
    public function getHeaderUrl()
    {
        $headerUrl = $this->domCrawler->filterXPath('//meta[@property="og:image"]')->attr('content');
        return $headerUrl;
    }

    public function getSquareUrl()
    {
        $squareUrl = $this->domCrawler->filterXPath('//vc-price-box-standard[@id="price-box-standard-content"]')->attr(':packshot-src');
        $squareUrl = str_replace("'", "", $squareUrl);
        return $squareUrl;
    }
}