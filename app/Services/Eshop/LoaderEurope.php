<?php

namespace App\Services\Eshop;

use GuzzleHttp\Client as GuzzleClient;

use App\EshopEuropeGame;

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

    private $expectedFields = [
        '_version_',
        'age_rating_sorting_i',
        'age_rating_type',
        'age_rating_value',
        'change_date',
        'cloud_saves_b',
        'club_nintendo',
        'compatible_controller',
        'copyright_s',
        'date_from',
        'dates_released_dts',
        'digital_version_b',
        'excerpt',
        'fs_id',
        'game_categories_txt',
        'game_category',
        'gift_finder_carousel_image_url_s',
        'gift_finder_detail_page_image_url_s',
        'gift_finder_wishlist_image_url_s',
        'image_url',
        'image_url_h2x1_s',
        'image_url_sq_s',
        'language_availability',
        'nsuid_txt',
        'originally_for_t',
        'pg_s',
        'physical_version_b',
        'play_mode_handheld_mode_b',
        'play_mode_tabletop_mode_b',
        'play_mode_tv_mode_b',
        'playable_on_txt',
        'players_to',
        'pretty_agerating_s',
        'pretty_date_s',
        'pretty_game_categories_txt',
        'price_discount_percentage_f',
        'price_has_discount_b',
        'price_lowest_f',
        'price_sorting_f',
        'product_code_txt',
        'publisher',
        'sorting_title',
        'system_names_txt',
        'system_type',
        'title',
        'title_extras_txt',
        'type',
        'url',
        'hd_rumble_b',
        'multiplayer_mode',
        'ir_motion_camera_b',
        'gift_finder_description_s',
        'players_from',
        'gift_finder_detail_page_store_link_s',
        'demo_availability',
        'paid_subscription_required_b',
        'internet',
        'add_on_content_b',
        'reg_only_hidden',
        'play_coins',
        'ranking_b',
        'match_play_b',
        'developer',
        'near_field_comm_b',
        'indie_b',
        'priority',
        'game_series_txt',
        'game_series_t',
        'local_play',
        'coop_play_b',
        'off_tv_play_b',
        'image_url_tm_s',
        'datasize_readable_txt',
        'mii_support',
        'voice_chat_b',
        'download_play',
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

    public function handleModelField($gameModel, $field, $value)
    {
        $booleanFields = [
            'club_nintendo',
            'price_has_discount_b',
            'play_mode_tv_mode_b',
            'play_mode_handheld_mode_b',
            'cloud_saves_b',
            'digital_version_b',
            'play_mode_tabletop_mode_b',
            'physical_version_b',
            'hd_rumble_b',
            'ir_motion_camera_b',
            'demo_availability',
            'paid_subscription_required_b',
            'internet',
            'add_on_content_b',
            'reg_only_hidden',
            'play_coins',
            'ranking_b',
            'match_play_b',
            'near_field_comm_b',
            'indie_b',
            'local_play',
            'coop_play_b',
            'off_tv_play_b',
            'mii_support',
            'voice_chat_b',
            'download_play',
        ];

        $jsonFields = [
            'dates_released_dts',
            'language_availability',
            'product_code_txt',
            'playable_on_txt',
            'pretty_game_categories_txt',
            'compatible_controller',
            'game_category',
            'system_names_txt',
            'title_extras_txt',
            'system_type',
            'game_categories_txt',
            'nsuid_txt',
            'game_series_txt',
            'datasize_readable_txt',
        ];

        $specialFields = [
            '_version_',
        ];

        if (!in_array($field, $this->expectedFields)) {
            throw new \Exception(sprintf('Cannot find field: %s - Value: %s', $field, $value));
        }

        if (in_array($field, $booleanFields)) {

            $gameModel->{$field} = $value == true ? 1 : 0;

        } elseif (in_array($field, $jsonFields)) {

            $gameModel->{$field} = json_encode($value);

        } elseif (in_array($field, $specialFields)) {

            if ($field == '_version_') {
                $gameModel->version = $value;
            }

        } else {

            $gameModel->{$field} = $value;

        }

        return $gameModel;
    }

    public function generateModel($eshopGame)
    {
        $gameModel = new EshopEuropeGame();

        foreach ($eshopGame as $key => $value) {
            $this->handleModelField($gameModel, $key, $value);
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

        try {

            foreach ($eshopGameData as $eshopGame) {

                $gameModel = $this->generateModel($eshopGame);
                $gameModel->save();

            }
        } catch (\Exception $e) {
            throw new \Exception('Error importing data! Message: '.$e->getMessage().'; Record: '.var_export($eshopGame, true));
        }
    }
}