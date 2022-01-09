<?php

namespace App\Services\DataSources\Wikipedia;

use App\Models\DataSourceRaw;
use Goutte\Client as GoutteClient;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Importer
{
    const WIKI_PAGE_LIST = 'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(A%E2%80%93F)';
    const WIKI_PAGE_LIST_2 = 'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(G%E2%80%93P)';
    const WIKI_PAGE_LIST_3 = 'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(Q%E2%80%93Z)';

    protected $wikiPageList = [
        'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(0%E2%80%939_and_A%E2%80%93B)',
        'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(C%E2%80%93G)',
        'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(H%E2%80%93P)',
        'https://en.wikipedia.org/wiki/List_of_Nintendo_Switch_games_(Q%E2%80%93Z)'
    ];

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

    // Used for testing only
    public function loadSingleRow($row)
    {
        $html = '<html><head><body><table id="softwarelist">'.$row.'</table></body></head></html>';
        $this->domCrawler = new DomCrawler();
        $this->domCrawler->addHtmlContent($html);
    }

    public function crawlExtractImportAll($logger, $sourceId)
    {
        foreach ($this->wikiPageList as $pageUrl) {
            $logger->info('Crawling page: '.$pageUrl);
            $this->domCrawler = $this->goutteClient->request('GET', $pageUrl);
            $this->extractRows();
            $this->importToDb($sourceId);
        }
    }

    public function crawlPage()
    {
        $this->domCrawler = $this->goutteClient->request('GET', self::WIKI_PAGE_LIST);
    }

    public function crawlPage2()
    {
        $this->domCrawler = $this->goutteClient->request('GET', self::WIKI_PAGE_LIST_2);
    }

    public function crawlPage3()
    {
        $this->domCrawler = $this->goutteClient->request('GET', self::WIKI_PAGE_LIST_3);
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
                        return $td->filter('span')->each(function ($span, $i) {
                            return trim($span->text());
                        });

                    } elseif ($spanCount == 1) {

                        if (strpos($td->text(), '-0000') !== false) {
                            // Clean it up first
                            $text = $td->text();
                            $text = str_replace("\r", '', $text);
                            $text = str_replace("\n", '', $text);
                            $text = trim($text);
                            // Fragile but it'll have to do
                            $output = explode('-0000', $text);
                            $output[0] .= '-0000';
                            return $output;
                        } else {
                            //throw new \Exception('Cannot handle field due to no available text matches ['.$td->text().']');
                            // Just return the text and see what happens
                            $text = $td->text();
                            $text = str_replace("\r", '', $text);
                            $text = str_replace("\n", '', $text);
                            $text = trim($text);
                            return $text;
                        }

                    } else {

                        // Not sure what to do here!
                        throw new \Exception('Unhandled span count: ' . $spanCount);

                    }

                } elseif (in_array($i, [2, 3])) {

                    // Developer, Publisher
                    $listItemCount = substr_count($td->html(), '<li>');

                    if ($listItemCount == 0) {

                        // Just return the text
                        return trim($td->text());

                    } else {

                        // Expected - we can deal with this
                        return $td->filter('li')->each(function ($span, $i) {
                            return trim($span->text());
                        });

                    }

                } elseif ($i == 1) {

                    // Genres
                    $listItemCount = substr_count($td->html(), '<li>');

                    if ($listItemCount == 0) {

                        // Just return the text
                        return trim($td->text());

                    } else {

                        // Expected - we can deal with this
                        return $td->filter('li')->each(function ($span, $i) {
                            return trim($span->text());
                        });

                    }

                } else {
                    return trim($td->text());
                }

            });
        });
    }

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
            $output = implode(',', $input);
        } else {
            $output = $input;
        }
        return $output;
    }

    public function importToDb($sourceId)
    {
        if (!$this->tableData) {
            throw new \Exception('Nothing to import!');
        }

        $counter = -1;

        $dateHandler = new DateHandler();

        foreach ($this->tableData as $row) {

            $counter++;

            // Skip the first two rows
            if (in_array($counter, [0, 1])) continue;

            $rowTitle = $this->limitField($row[0], 150);
            $rowGenres = $this->flattenArray($row[1]);
            $rowDevs = $this->flattenArray($row[2]);
            $rowPubs = $this->flattenArray($row[3]);

            // Make sure we've got a title
            if (!$rowTitle) continue;

            // Release dates
            $rowErrorData = $rowTitle . ',' . $rowDevs . ',' . $rowPubs; // used if the date fails
            $jpReleaseDate = $dateHandler->getReleaseDate($row[4], $rowErrorData);
            $usReleaseDate = $dateHandler->getReleaseDate($row[5], $rowErrorData);
            $euReleaseDate = $dateHandler->getReleaseDate($row[6], $rowErrorData);

            // Prepare raw data
            $sourceItem = [
                'genres' => $rowGenres,
                'developers' => $rowDevs,
                'publishers' => $rowPubs,
                'release_date_eu' => $euReleaseDate,
                'release_date_us' => $usReleaseDate,
                'release_date_jp' => $jpReleaseDate,
            ];

            // Save raw data
            $dataSourceRaw = new DataSourceRaw();
            $dataSourceRaw->source_id = $sourceId;
            $dataSourceRaw->title = $rowTitle;
            $dataSourceRaw->source_data_json = json_encode($sourceItem);
            $dataSourceRaw->save();

        }

        $this->importedItemCount = $counter;
    }
}