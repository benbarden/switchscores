<?php

namespace App\Services\Eshop\US;

use App\Models\EshopUSGame;
use GuzzleHttp\Client as GuzzleClient;

class Loader
{
    const MODE_TEST = 1;
    const MODE_LIVE = 2;

    const DEFAULT_LOCALE = "en";
    const GET_GAMES_US_URL = "https://www.nintendo.com/json/content/get/filter/game";

    private $expectedFields = [
        'categories',
        'slug',
        'buyitnow',
        'release_date',
        'digitaldownload',
        'nso',
        'free_to_start',
        'title',
        'system',
        'id',
        'ca_price',
        'number_of_players',
        'nsuid',
        'video_link',
        'eshop_price',
        'front_box_art',
        'game_code',
        'buyonline',
        'sale_price',
        'release_date_display',
    ];

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

    public function getExpectedFields()
    {
        return $this->expectedFields;
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

    public function clearResponseData()
    {
        $this->responseData = [];
    }

    public function getImportedCount()
    {
        return $this->importedItemCount;
    }

    public function handleModelField($gameModel, $field, $value)
    {
        $booleanFields = [
            'buyitnow',
            'digitaldownload',
            'nso',
            'free_to_start',
            'buyonline',
        ];

        $jsonFields = [
            'categories',
        ];

        $dateFields = [
            'release_date',
        ];

        $specialFields = [
            'id',
        ];

        if (!in_array($field, $this->expectedFields)) {
            throw new \Exception(sprintf('Cannot find field: %s - Value: %s', $field, $value));
        }

        if (in_array($field, $booleanFields)) {

            $gameModel->{$field} = $value == true ? 1 : 0;

        } elseif (in_array($field, $jsonFields)) {

            $gameModel->{$field} = json_encode($value);

        } elseif (in_array($field, $dateFields)) {

            $gameModel->{$field} = date('Y-m-d', strtotime($value));

        } elseif (in_array($field, $specialFields)) {

            if ($field == 'id') {
                // We can't call it "id" or it clashes with the primary key of the table
                $gameModel->ncom_id = $value;
            }

        } else {

            $gameModel->{$field} = $value;

        }

        return $gameModel;
    }

    public function generateModel($eshopGame)
    {
        $gameModel = new EshopUSGame();

        foreach ($eshopGame as $key => $value) {
            $this->handleModelField($gameModel, $key, $value);
        }

        return $gameModel;
    }

    public function loadLocalData($filePath)
    {
        $json = file_get_contents($filePath);
        $this->responseData = json_decode($json, true);
    }

    public function loadGames($offset = 0, $limit = 200)
    {
        // Request url
        $baseUrl = self::GET_GAMES_US_URL;
        $queryString = '?system=switch&sort=title&direction=asc&limit='.$limit.'&offset='.$offset;

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

    public function importToDb()
    {
        if (!$this->responseData) {
            throw new \Exception('Nothing to import!');
        }

        $eshopGame = null;
        $eshopGameData = $this->responseData['games'];

        $totalItemCount = count($eshopGameData['game']);

        try {

            foreach ($eshopGameData['game'] as $eshopGame) {

                $gameModel = $this->generateModel($eshopGame);
                $gameModel->save();

            }
        } catch (\Exception $e) {
            throw new \Exception('Error importing data! Message: '.$e->getMessage().'; Record: '.var_export($eshopGame, true));
        }

        $this->importedItemCount = $totalItemCount;
    }
}