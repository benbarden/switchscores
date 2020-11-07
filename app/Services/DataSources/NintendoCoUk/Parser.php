<?php


namespace App\Services\DataSources\NintendoCoUk;

use App\DataSourceRaw;
use App\DataSourceParsed;
use Illuminate\Log\Logger;

class Parser
{
    /**
     * @var DataSourceParsed
     */
    private $dataSourceParsed;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $rawJsonData;

    public function __construct(DataSourceRaw $dataSourceRaw, $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        }

        $dataSourceParsed = new DataSourceParsed();

        $dataSourceParsed->source_id = $dataSourceRaw->source_id;
        $dataSourceParsed->title = $dataSourceRaw->title;

        $this->dataSourceParsed = $dataSourceParsed;
        $this->rawJsonData = json_decode($dataSourceRaw->source_data_json, true);
    }

    /**
     * @return DataSourceParsed
     */
    public function parseItem()
    {
        // fs_id
        $parsedFsId = $this->parseFsId();
        if (!is_null($parsedFsId)) {
            $this->dataSourceParsed->link_id = $parsedFsId;
        }

        // URL
        $parsedUrl = $this->parseUrl();
        if (!is_null($parsedUrl)) {
            $this->dataSourceParsed->url = $parsedUrl;
        }

        // Price
        $priceData = $this->parsePrice();
        list($priceStandard, $priceDiscounted, $priceDiscountPc) = $priceData;
        if (!is_null($priceStandard)) {
            $this->dataSourceParsed->price_standard = $priceStandard;
        }
        if (!is_null($priceDiscounted)) {
            $this->dataSourceParsed->price_discounted = $priceDiscounted;
        }
        if (!is_null($priceDiscountPc)) {
            $this->dataSourceParsed->price_discount_pc = $priceDiscountPc;
        }

        // Release date
        $parsedReleaseDate = $this->parseReleaseDate();
        if (!is_null($parsedReleaseDate)) {
            $this->dataSourceParsed->release_date_eu = $parsedReleaseDate;
        }

        // Publishers
        $parsedPublishers = $this->parsePublishers();
        if (!is_null($parsedPublishers)) {
            $this->dataSourceParsed->publishers = $parsedPublishers;
        }

        // Players
        $parsedPlayers = $this->parsePlayers();
        if (!is_null($parsedPlayers)) {
            $this->dataSourceParsed->players = $parsedPlayers;
        }

        // Genres
        $parsedGenres = $this->parseGenres();
        if (!is_null($parsedGenres)) {
            $this->dataSourceParsed->genres_json = $parsedGenres;
        }

        // Images
        $parsedImageSquare = $this->parseImageSquare();
        $parsedImageHeader = $this->parseImageHeader();
        if (!is_null($parsedImageSquare)) {
            $this->dataSourceParsed->image_square = $parsedImageSquare;
        }
        if (!is_null($parsedImageHeader)) {
            $this->dataSourceParsed->image_header = $parsedImageHeader;
        }

        return $this->dataSourceParsed;
    }

    public function parseFsId()
    {
        $parsedFsId = null;

        if (array_key_exists('fs_id', $this->rawJsonData)) {
            $parsedFsId = $this->rawJsonData['fs_id'];
        }

        return $parsedFsId;
    }

    public function parseUrl()
    {
        $parsedUrl = null;

        if (array_key_exists('url', $this->rawJsonData)) {
            $parsedUrl = $this->rawJsonData['url'];
        }

        return $parsedUrl;
    }

    /**
     * @return array
     * Returns an array with format:
        ['price_standard', 'price_discounted', 'price_discount_pc']
     */
    public function parsePrice()
    {
        $priceStandard = null;
        $priceDiscounted = null;
        $priceDiscountPc = null;

        if (array_key_exists('price_regular_f', $this->rawJsonData)) {
            $rawPriceRegularF = $this->rawJsonData['price_regular_f'];
        } else {
            $rawPriceRegularF = null;
        }
        if (array_key_exists('price_lowest_f', $this->rawJsonData)) {
            $rawPriceLowestF = $this->rawJsonData['price_lowest_f'];
        } else {
            $rawPriceLowestF = null;
        }
        if (array_key_exists('price_discount_percentage_f', $this->rawJsonData)) {
            $rawPriceDiscountPercentageF = $this->rawJsonData['price_discount_percentage_f'];
        } else {
            $rawPriceDiscountPercentageF = '0.0';
        }

        // Store discount percentage
        if ($rawPriceDiscountPercentageF != '0.0') {
            $priceDiscountPc = $rawPriceDiscountPercentageF;
        }

        // Store standard price
        if (!is_null($rawPriceRegularF)) {
            $priceStandard = number_format($rawPriceRegularF, 2);
        }

        // Handle discounts
        if (!is_null($priceDiscountPc) && ($rawPriceLowestF > 0)) {
            $priceDiscounted = number_format($rawPriceLowestF, 2);
        }

        return [$priceStandard, $priceDiscounted, $priceDiscountPc];
    }

    public function parseReleaseDate()
    {
        $parsedReleaseDate = null;

        if (!array_key_exists('pretty_date_s', $this->rawJsonData)) {
            return null;
        }
        $rawReleaseDate = $this->rawJsonData['pretty_date_s'];

        try {
            $eshopReleaseDateObj = \DateTime::createFromFormat('d/m/Y', $rawReleaseDate);
            $parsedReleaseDate = $eshopReleaseDateObj->format('Y-m-d');
        } catch (\Throwable $e) {
            // Date error
        }

        return $parsedReleaseDate;
    }

    public function parsePublishers()
    {
        $parsedPublishers = null;

        if (array_key_exists('publisher', $this->rawJsonData)) {

            $parsedPublishers = $this->rawJsonData['publisher'];

            // Clean up junk text
            $parsedPublishers = str_replace('Co.,Ltd', '', $parsedPublishers);
            $parsedPublishers = str_replace('Co., Ltd.', '', $parsedPublishers);
            $parsedPublishers = str_replace('Co. Ltd', '', $parsedPublishers);
            $parsedPublishers = str_replace('CO.,LTD.', '', $parsedPublishers);
            $parsedPublishers = str_replace('CO., LTD', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Ltd.', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Ltd', '', $parsedPublishers);
            $parsedPublishers = str_replace(' LTD.', '', $parsedPublishers);
            $parsedPublishers = str_replace(' LTD', '', $parsedPublishers);
            $parsedPublishers = str_replace(' LIMITED', '', $parsedPublishers);
            $parsedPublishers = str_replace(', S.L.', '', $parsedPublishers);
            $parsedPublishers = str_replace(', s.r.o.', '', $parsedPublishers);
            $parsedPublishers = str_replace(' S.r.l.', '', $parsedPublishers);
            $parsedPublishers = str_replace(', LLC', '', $parsedPublishers);
            $parsedPublishers = str_replace(' LLC', '', $parsedPublishers);
            $parsedPublishers = str_replace('LLC ', '', $parsedPublishers);
            $parsedPublishers = str_replace(', Incorporated', '', $parsedPublishers);
            $parsedPublishers = str_replace(', Inc.', '', $parsedPublishers);
            $parsedPublishers = str_replace(', Inc', '', $parsedPublishers);
            $parsedPublishers = str_replace(', inc', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Inc.', '', $parsedPublishers);
            $parsedPublishers = str_replace('®', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Sp. z.o.o Sp.K', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Sp. z.o.o', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Sp.z.o.o', '', $parsedPublishers);
            $parsedPublishers = str_replace(' sp. z.o.o', '', $parsedPublishers);
            $parsedPublishers = str_replace(' S.R.L', '', $parsedPublishers);
            $parsedPublishers = str_replace(' srl', '', $parsedPublishers);
            $parsedPublishers = str_replace(' s.r.o', '', $parsedPublishers);
            $parsedPublishers = str_replace(' S.L', '', $parsedPublishers);
            $parsedPublishers = str_replace(' G.K', '', $parsedPublishers);
            $parsedPublishers = str_replace(' B.V', '', $parsedPublishers);
            $parsedPublishers = str_replace(' d.o.o', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Pty', '', $parsedPublishers);
            $parsedPublishers = str_replace(' Pty Ltd', '', $parsedPublishers);
            $parsedPublishers = str_replace(' (Pty.)', '', $parsedPublishers);
            $parsedPublishers = str_replace(' PTY', '', $parsedPublishers);
            $parsedPublishers = str_replace(' FK AB', '', $parsedPublishers);
            $parsedPublishers = str_replace(' GmbH', '', $parsedPublishers);

            // Consistency
            $parsedPublishers = str_replace(' ENTMT', ' Entertainment', $parsedPublishers);
            $parsedPublishers = str_replace(' Ent.', ' Entertainment', $parsedPublishers);
            //$parsedPublishers = str_replace(' Ent', ' Entertainment', $parsedPublishers);
            $parsedPublishers = rtrim($parsedPublishers, ".");

            if (is_array($parsedPublishers)) {
                $publisherArray = $parsedPublishers;
            } elseif (strpos($parsedPublishers, ",") !== false) {
                $publisherArray = explode(",", $parsedPublishers);
            } else {
                $publisherArray = [];
                array_push($publisherArray, $parsedPublishers);
            }
            foreach ($publisherArray as &$item) {
                $item = trim($item);
            }
            sort($publisherArray);
            $parsedPublishers = implode(",", $publisherArray);

        }

        return $parsedPublishers;
    }

    public function parsePlayers()
    {
        $parsedPlayers = null;

        $playersFrom = null;
        $playersTo = null;

        if (array_key_exists('players_from', $this->rawJsonData)) {
            $playersFrom = $this->rawJsonData['players_from'];
        } else {
            $playersFrom = 1;
        }
        if (!$playersFrom) $playersFrom = 1;

        if (array_key_exists('players_to', $this->rawJsonData)) {
            $playersTo = $this->rawJsonData['players_to'];
        } else {
            $playersTo = 1;
        }
        if (!$playersTo) $playersTo = 1;

        // Same values
        if ($playersFrom == $playersTo) {

            $parsedPlayers = $playersTo;

        } else {

            // Standard format
            $parsedPlayers = sprintf('%s-%s', $playersFrom, $playersTo);

        }

        return $parsedPlayers;
    }

    public function parseGenres()
    {
        if (!array_key_exists('pretty_game_categories_txt', $this->rawJsonData)) {
            return null;
        }

        $parsedGenres = json_encode($this->rawJsonData['pretty_game_categories_txt']);

        return $parsedGenres;
    }

    public function parseImageSquare()
    {
        if (!array_key_exists('image_url_sq_s', $this->rawJsonData)) {
            return null;
        }

        $parsedImage = $this->rawJsonData['image_url_sq_s'];

        return $parsedImage;
    }

    public function parseImageHeader()
    {
        if (!array_key_exists('image_url_h2x1_s', $this->rawJsonData)) {
            return null;
        }

        $parsedImage = $this->rawJsonData['image_url_h2x1_s'];

        return $parsedImage;
    }
}