<?php

namespace App\Domain\DataSource\NintendoCoUk;

use Symfony\Component\Panther\Client;
use Symfony\Component\DomCrawler\Crawler;
use Facebook\WebDriver\Exception\StaleElementReferenceException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class ImportSearchPage
{
    const SEARCH_RECENTLY_RELEASED_BASE = '/en-gb/Search/Search-299117.html?f=147394-16-74';

    public function loadHtml($page = 1)
    {
        $client = Client::createChromeClient(null, ['--headless'], [
            'timeout_in_seconds' => 30,
        ]);

        $pageUrl = 'https://www.'.'nin'.'ten'.'do'.'.com'.self::SEARCH_RECENTLY_RELEASED_BASE;
        if ($page > 1) {
            $pageUrl .= '&p='.$page;
        }

        // Navigate to the page
        $client->request('GET', $pageUrl);

        // Xpath for the results list
        $xpathRoot = '//div[@id="VA_MainSearch"]//ul[@class="results"]';

        // Wait for the page to load completely
        $client->wait(30)->until(function() use ($client) {
            return str_contains($client->getPageSource(), 'data-nt-item-title');
        });

        // Clear cookies and session storage to avoid stale sessions
        $client->executeScript('window.localStorage.clear(); window.sessionStorage.clear(); document.cookie.split(";").forEach(function(c) { document.cookie = c.trim().split("=")[0] + "=;expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/"; });');

        // Scroll to the bottom to load more items
        $client->executeScript('window.scrollTo(0, document.body.scrollHeight);');

        // Wait for the updated page to load
        $client->wait(30)->until(function() use ($client) {
            return str_contains($client->getPageSource(), 'data-nt-item-title');
        });

        // Fetch the updated page source after scrolling
        file_put_contents(storage_path('debug-final.html'), $client->getPageSource());

        $crawler = $client->getCrawler();

        $items = [];

        $crawler->filterXPath($xpathRoot.'/li')->each(function(Crawler $item) use(&$items) {

            $title = $item->attr('data-nt-item-title') ?: 'UNKNOWN';

            // Use relative XPath within the item to locate the anchor and image
            $anchor = $item->filterXPath('.//a');
            $img = $item->filterXPath('.//img[@class="img-responsive"]');

            $dateNode = $item->filterXPath('.//div[contains(@class, "search-result-txt")]/div/p[@class="page-data"]/text()[normalize-space()][preceding-sibling::text()[normalize-space() = "•"]]/..');
            $dateText = $dateNode->count() > 0 ? $dateNode->text() : null;

            // Use PHP to extract the date after the "•" symbol
            $date = $dateText ? trim(explode('•', $dateText)[1]) : null;

            $newItem = [
                'title' => $title,
                'href' => $anchor->count() > 0 ? $anchor->attr('href') : null,
                'img' => $img->count() > 0 ? $img->attr('src') : null,
                'date' => $date
            ];

            $items[] = $newItem;
        });

        return $items;

    }

}