<?php

namespace App\Services\Eshop;

use App\Services\SiteAlertService;
use App\SiteAlert;
use GuzzleHttp\Client as GuzzleClient;

use App\EshopEuropeGame;
use App\Services\Eshop\Europe\FieldMapper;

/**
 * Class LoaderEurope
 * @package App\Services\Eshop
*/
class LoaderEurope
{
    const MODE_TEST = 1;
    const MODE_LIVE = 2;

    const DEFAULT_LOCALE = "en";
    const GET_GAMES_EU_URL = "http://search.nintendo-europe.com/{locale}/select";

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

    public function handleModelField($gameModel, $field, $value)
    {
        $fieldMapper = new FieldMapper();
        $fieldMapper->setField($field);

        if (!$fieldMapper->fieldExists()) {

            if (is_array($value)) {
                $value = json_encode($value);
            }
            $errorMsg = 'Cannot find field: '.$field.' - Value: '.$value;

            // We only need one error report per field per run
            if (in_array($errorMsg, $this->alreadyAlerted)) {
                return false;
            } else {
                $this->alreadyAlerted[] = $errorMsg;
            }

            // OK to proceed
            $serviceSiteAlert = new SiteAlertService();
            $serviceSiteAlert->create(SiteAlert::TYPE_ERROR, __CLASS__, $errorMsg);
            return false;
        }

        $dbFieldName = $fieldMapper->getDbFieldName();
        $dbFieldType = $fieldMapper->getDbFieldType();

        if ($dbFieldType == FieldMapper::TYPE_BOOLEAN) {

            if ($value == true) {
                $fieldValue = 1;
            } elseif ($value == NULL) {
                $fieldValue = 0;
            } else {
                $fieldValue = 0;
            }
            $gameModel->{$dbFieldName} = $fieldValue;

        } elseif ($dbFieldType == FieldMapper::TYPE_JSON) {

            $gameModel->{$dbFieldName} = json_encode($value);

        } else {

            $gameModel->{$dbFieldName} = $value;

        }

        return $gameModel;
    }

    public function generateModel($eshopGame)
    {
        $gameModel = new EshopEuropeGame();

        foreach ($eshopGame as $key => $value) {
            try {
                $this->handleModelField($gameModel, $key, $value);
            } catch (\Exception $e) {
                continue;
            }
        }

        return $gameModel;
    }

    public function loadLocalData($file)
    {
        $json = file_get_contents(dirname(__FILE__).'/../../../storage/eshop/'.$file);
        $this->responseData = json_decode($json, true);
    }

    public function loadGames($limit = 9999, $locale = 'en')
    {
        /*
         * TEST URL
         http://search.nintendo-europe.com/en/select
         ?fq=type:GAME AND system_type:nintendoswitch* AND product_code_txt:*&q=*&rows=10&sort=sorting_title asc&start=0&wt=json
         */

        // Base url
        $baseUrl = str_replace('{locale}', $locale, self::GET_GAMES_EU_URL);

        // Build query string
        $qsFq = "type:GAME AND system_type:nintendoswitch* AND product_code_txt:*";
        $qsQ = "*";
        $qsSort = "sorting_title asc";
        $qsStart = "0";
        $qsWt = "json";

        $queryString = sprintf('?fq=%s&q=%s&rows=%s&sort=%s&start=%s&wt=%s',
            $qsFq, $qsQ, $limit, $qsSort, $qsStart, $qsWt
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

    public function importToDb()
    {
        if (!$this->responseData) {
            throw new \Exception('Nothing to import!');
        }

        $eshopGame = null;
        $eshopGameData = $this->responseData['response']['docs'];

        $totalItemCount = count($eshopGameData);

        try {

            foreach ($eshopGameData as $eshopGame) {

                $gameModel = $this->generateModel($eshopGame);
                $gameModel->save();

            }
        } catch (\Exception $e) {
            throw new \Exception('Error importing data! Message: '.$e->getMessage().'; Record: '.var_export($eshopGame, true));
        }

        $this->importedItemCount = $totalItemCount;
    }
}