<?php


namespace App\Services\HtmlLoader\Wikipedia;

use Goutte\Client as GoutteClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;


class Crawler
{
    const WIKI_PAGE_LIST = 'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games';

    /**
     * @var GoutteClient
     */
    private $goutteClient;

    /**
     * @var DomCrawler
     */
    private $domCrawler;

    /**
     * @var array
     */
    private $tableData;

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

    public function loadLocalData($file)
    {
        $html = file_get_contents(dirname(__FILE__).'/../../../../storage/crawler/'.$file);
        if (!$html) {
            throw new \Exception('Failed to load file: '.$file);
        }
        $this->domCrawler = new DomCrawler();
        $this->domCrawler->addHtmlContent($html);
    }

    public function loadSingleRow($row)
    {
        $html = '<html><head><body><table id="softwarelist">'.$row.'</table></body></head></html>';
        $this->domCrawler = new DomCrawler();
        $this->domCrawler->addHtmlContent($html);
    }

    public function crawlPage()
    {
        $this->domCrawler = $this->goutteClient->request('GET', self::WIKI_PAGE_LIST);
    }

    public function extractRows()
    {
        $this->tableData = $this->domCrawler->filter('table#softwarelist')->filter('tr')->each(function ($tr, $i) {

            return $tr->filter('td, th')->each(function ($td, $i) {

                if (in_array($i, [4, 5, 6])) {

                    // Different processing for date fields
                    $spanCount = substr_count($td->html(), '<span ');

                    if ($spanCount == 0) {

                        // Just return the text
                        return trim($td->text());

                    } elseif ($spanCount == 2) {

                        // Expected - we can deal with this
                        return $td->filter('span')->each(function($span, $i) {
                            return trim($span->text());
                        });

                    } elseif ($spanCount == 1) {

                        if (strpos($td->text(), '-0000') !== false) {
                            // Fragile but it'll have to do
                            $output = explode('-0000', $td->text());
                            $output[0] .= '-0000';
                            return $output;
                        } else {
                            // Not sure what to do here!
                            throw new \Exception('Cannot handle field due to no available text matches');
                        }

                    } else {

                        // Not sure what to do here!
                        throw new \Exception('Unhandled span count: '.$spanCount);

                    }

                    /*
                    if (strpos($td->html(), '<span ') !== false) {
                        return $td->filter('span')->each(function($span, $i) {
                            return trim($span->text());
                        });
                    } else {
                        return trim($td->text());
                    }
                    */

                } else {
                    return trim($td->text());
                }

            });
        });
    }

}