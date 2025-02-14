<?php

namespace App\Services\DataSources\NintendoCoUk;

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

    public function loadLocalData($file)
    {
        $json = file_get_contents(dirname(__FILE__).'/../../../storage/eshop/'.$file);
        $this->responseData = json_decode($json, true);
    }

    public function loadGames($limit = 1000, $start = 0, $locale = 'en')
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

        // Build query string
        $qsFq = "type:GAME AND system_type:nintendoswitch* AND product_code_txt:*";
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

    public function importToDb($sourceId)
    {
        if (!$this->responseData) {
            throw new \Exception('Nothing to import!');
        }

        $sourceItem = null;
        $rawSourceData = $this->responseData['response']['docs'];

        $totalItemCount = count($rawSourceData);

        try {

            foreach ($rawSourceData as $sourceItem) {

                if (!array_key_exists('title', $sourceItem)) continue;

                $dataSourceRaw = new DataSourceRaw();
                $dataSourceRaw->source_id = $sourceId;
                $dataSourceRaw->title = $sourceItem['title'];
                $dataSourceRaw->source_data_json = json_encode($sourceItem);
                $dataSourceRaw->save();

            }
        } catch (\Exception $e) {
            throw new \Exception('Error importing data! Message: '.$e->getMessage().'; Record: '.var_export($sourceItem, true));
        }

        $this->importedItemCount = $totalItemCount;
    }
}