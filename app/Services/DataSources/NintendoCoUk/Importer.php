<?php

namespace App\Services\DataSources\NintendoCoUk;

use App\Models\Console;
use App\Models\DataSourceRaw;
use GuzzleHttp\Client as GuzzleClient;

class Importer
{
    const MODE_TEST = 1;
    const MODE_LIVE = 2;

    const DEFAULT_LOCALE = "en";
    const GET_GAMES_EU_URL = "https://search.nintendo-europe.com/{locale}/select";

    /**
     * @var array
     */
    private $alreadyAlerted = [];

    /**
     * @var integer
     */
    private $loaderMode;

    /**
     * @var string
     */
    private $requestUrl;

    /**
     * @var integer
     */
    private $httpStatus;

    /**
     * @var array
     */
    private $responseData;

    /**
     * @var integer
     */
    private $importedItemCount;

    public function __construct()
    {
        $this->setModeLive();
    }

    private function setMode($mode)
    {
        $this->loaderMode = $mode;
    }

    public function setModeTest()
    {
        $this->setMode(self::MODE_TEST);
    }

    public function setModeLive()
    {
        $this->setMode(self::MODE_LIVE);
    }

    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getResponseData()
    {
        return $this->responseData;
    }

    public function getImportedCount()
    {
        return $this->importedItemCount;
    }

    public function getNumFound()
    {
        return $this->responseData['response']['numFound'] ?? null;
    }

    public function loadLocalData($file)
    {
        $json = file_get_contents(dirname(__FILE__).'/../../../storage/eshop/'.$file);
        $this->responseData = json_decode($json, true);
    }

    public function loadGamesSwitch2($limit = 1000, $start = 0, $locale = 'en')
    {
        $this->loadGames($limit, $start, $locale, 'switch2');
    }

    public function loadGames($limit = 1000, $start = 0, $locale = 'en', $systemType = '')
    {
        /*
         * TEST URL
         https://search.nintendo-europe.com/en/select
         ?fq=type:GAME AND system_type:nintendoswitch* AND product_code_txt:*
         &q=*&rows=10&sort=sorting_title asc&start=0&wt=json
         */
        /*
         https://search.nintendo-europe.com/en/select
         ?fq=type:GAME AND system_type:nintendoswitch* AND product_code_txt:*
         &q=*&rows=10&sort=sorting_title asc&start=0&wt=json
         */

        // Base url
        $baseUrl = str_replace('{locale}', $locale, self::GET_GAMES_EU_URL);

        // System type
        if ($systemType == 'switch2') {
            $systemQuery = '"nintendoswitch2"';
        } else {
            // Exclude Switch 2 games from Switch 1 query to prevent duplicates
            $systemQuery = '(nintendoswitch* AND -system_type:"nintendoswitch2")'." AND product_code_txt:*";
        }

        // Build query string
        $qsFq = "type:GAME AND system_type:".$systemQuery;
        $qsQ = "*";
        $qsSort = "sorting_title asc";
        //$qsStart = "0";
        $qsWt = "json";

        $queryString = sprintf('?fq=%s&q=%s&rows=%s&sort=%s&start=%s&wt=%s',
            $qsFq, $qsQ, $limit, $qsSort, $start, $qsWt
        );

        // Request url
        $requestUrl = $baseUrl.$queryString;
        $this->requestUrl = $requestUrl;

        // Load the data
        try {
            $client = new GuzzleClient();
            $response = $client->request('GET', $requestUrl);
        } catch (\Exception $e) {
            throw new \Exception('Failed to load feed URL! Error: '.$e->getMessage());
        }

        try {
            $statusCode = $response->getStatusCode();
            $body = $response->getBody();

            $this->httpStatus = $statusCode;
            //$this->responseBody = $body;

        } catch (\Exception $e) {
            throw new \Exception('Failed to load feed URL! Status code: '.$statusCode.'Error: '.$e->getMessage());
        }

        if ($statusCode != 200) {
            throw new \Exception('Cannot load feed: '.$requestUrl);
        }

        try {
            $this->responseData = json_decode($body, true);
        } catch (\Exception $e) {
            throw new \Exception('Error loading data! Error details: '.$e->getMessage().'; Raw data: '.$body);
        }
    }

    /**
     * Upsert raw records from the last loaded response.
     *
     * Returns an array with:
     *   'new_ids'      => IDs of newly inserted records (need parsing)
     *   'changed_ids'  => IDs of records whose content hash changed (need parsing)
     *   'relisted_ids' => IDs of records that were is_delisted=1 but reappeared
     */
    public function importToDb($sourceId): array
    {
        if (!$this->responseData) {
            throw new \Exception('Nothing to import!');
        }

        $sourceItem = null;
        $rawSourceData = $this->responseData['response']['docs'];

        $newIds      = [];
        $changedIds  = [];
        $relistedIds = [];
        $now = now();

        try {

            foreach ($rawSourceData as $sourceItem) {

                if (!array_key_exists('title', $sourceItem)) continue;
                if (!array_key_exists('system_type', $sourceItem)) continue;

                $systemType = $sourceItem['system_type'][0];
                if ($systemType == 'nintendoswitch2') {
                    $consoleId = Console::ID_SWITCH_2;
                } elseif ($systemType == 'nintendoswitch2,nintendoswitch2') {
                    $consoleId = Console::ID_SWITCH_2;
                } else {
                    $consoleId = Console::ID_SWITCH_1;
                }

                $linkId      = $sourceItem['fs_id'] ?? null;
                $jsonString  = json_encode($sourceItem);
                $contentHash = md5($jsonString);

                $existing = $linkId
                    ? DataSourceRaw::where('source_id', $sourceId)->where('link_id', $linkId)->first()
                    : null;

                if (!$existing) {
                    $record = new DataSourceRaw();
                    $record->source_id        = $sourceId;
                    $record->console_id       = $consoleId;
                    $record->link_id          = $linkId;
                    $record->title            = $sourceItem['title'];
                    $record->source_data_json = $jsonString;
                    $record->content_hash     = $contentHash;
                    $record->last_seen_at     = $now;
                    $record->is_delisted      = 0;
                    $record->save();
                    $newIds[] = $record->id;
                } else {
                    if ($existing->is_delisted) {
                        $relistedIds[] = $existing->id;
                    }

                    if ($existing->content_hash !== $contentHash) {
                        $existing->console_id       = $consoleId;
                        $existing->title            = $sourceItem['title'];
                        $existing->source_data_json = $jsonString;
                        $existing->content_hash     = $contentHash;
                        $changedIds[] = $existing->id;
                    }

                    $existing->last_seen_at = $now;
                    $existing->is_delisted  = 0;
                    $existing->save();
                }
            }

        } catch (\Exception $e) {
            throw new \Exception('Error importing data! Message: '.$e->getMessage().'; Record: '.var_export($sourceItem, true));
        }

        $this->importedItemCount = count($rawSourceData);

        return [
            'new_ids'      => $newIds,
            'changed_ids'  => $changedIds,
            'relisted_ids' => $relistedIds,
        ];
    }
}