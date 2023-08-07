<?php

namespace App\Domain\Scraper;

use Symfony\Component\BrowserKit\HttpBrowser as ScraperClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Base
{
    /**
     * @var ScraperClient
     */
    protected $scraperClient;

    /**
     * @var DomCrawler
     */
    protected $domCrawler;

    /**
     * @var array
     */
    protected $tableData;

    public function __construct()
    {
        $this->scraperClient = new ScraperClient();
    }

    public function __destruct()
    {
        unset($this->scraperClient);
    }

    public function getClient()
    {
        return $this->scraperClient;
    }

    public function getDomCrawler()
    {
        return $this->domCrawler;
    }

    public function getTableData()
    {
        return $this->tableData;
    }

    public function crawlPage($url)
    {
        $this->domCrawler = $this->scraperClient->request('GET', $url);
    }

    public function crawlHtml($html)
    {
        $html = '<html><head><body>'.$html.'</body></head></html>';
        $this->domCrawler = new DomCrawler();
        $this->domCrawler->addHtmlContent($html);
    }
}