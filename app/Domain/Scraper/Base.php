<?php

namespace App\Domain\Scraper;

use Goutte\Client as GoutteClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Base
{
    /**
     * @var GoutteClient
     */
    protected $goutteClient;

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
        $this->goutteClient = new GoutteClient();
    }

    public function __destruct()
    {
        unset($this->goutteClient);
    }

    public function getClient()
    {
        return $this->goutteClient;
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
        $this->domCrawler = $this->goutteClient->request('GET', $url);
    }

    public function crawlHtml($html)
    {
        $html = '<html><head><body>'.$html.'</body></head></html>';
        $this->domCrawler = new DomCrawler();
        $this->domCrawler->addHtmlContent($html);
    }
}