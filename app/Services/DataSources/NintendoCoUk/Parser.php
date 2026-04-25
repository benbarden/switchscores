<?php


namespace App\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\DataSourceRaw;
use Illuminate\Log\Logger;

class Parser
{
    /**
     * @var \App\Models\DataSourceParsed
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

    private ?array $oldFieldValues = null;

    public function __construct()
    {
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function setDataSourceRaw(DataSourceRaw $dataSourceRaw)
    {
        $existing = DataSourceParsed::where('source_id', $dataSourceRaw->source_id)
            ->where('link_id', $dataSourceRaw->link_id)
            ->first();

        // Snapshot field values before any changes; null means this is a new record
        $this->oldFieldValues = $existing ? $this->snapshotFields($existing) : null;

        $dataSourceParsed = $existing ?? new DataSourceParsed();

        $dataSourceParsed->source_id  = $dataSourceRaw->source_id;
        $dataSourceParsed->console_id = $dataSourceRaw->console_id;
        $dataSourceParsed->title      = $dataSourceRaw->title;
        $dataSourceParsed->is_delisted = 0;
        // game_id is preserved if $existing already has one

        $this->dataSourceParsed = $dataSourceParsed;
        $this->rawJsonData = json_decode($dataSourceRaw->source_data_json, true);
    }

    /**
     * Returns a field-level diff of parsed values compared to the previous snapshot.
     * Returns null for new records (no previous values to compare against).
     * Returns null if no parsed fields changed.
     * If new raw fields are added to parsed records in future, add them to snapshotFields().
     */
    public function getChangedFields(): ?array
    {
        if ($this->oldFieldValues === null) {
            return null;
        }

        $newValues = $this->snapshotFields($this->dataSourceParsed);
        $diff = [];

        foreach ($this->oldFieldValues as $field => $oldValue) {
            $newValue = $newValues[$field] ?? null;
            if ($this->valuesAreDifferent($oldValue, $newValue)) {
                $diff[$field] = ['from' => $oldValue, 'to' => $newValue];
            }
        }

        return empty($diff) ? null : $diff;
    }

    private function snapshotFields(DataSourceParsed $item): array
    {
        return [
            'title'                => $item->title,
            'console_id'           => $item->console_id,
            'url'                  => $item->url,
            'price_standard'       => $item->price_standard,
            'price_discounted'     => $item->price_discounted,
            'price_discount_pc'    => $item->price_discount_pc,
            'release_date_eu'      => $item->release_date_eu,
            'release_date_us'      => $item->release_date_us,
            'release_date_jp'      => $item->release_date_jp,
            'developers'           => $item->developers,
            'publishers'           => $item->publishers,
            'players'              => $item->players,
            'genres_json'          => $item->genres_json,
            'image_square'         => $item->image_square,
            'image_header'         => $item->image_header,
            'has_physical_version' => $item->has_physical_version,
            'has_dlc'              => $item->has_dlc,
            'has_demo'             => $item->has_demo,
            'is_delisted'          => $item->is_delisted,
        ];
    }

    private function valuesAreDifferent($a, $b): bool
    {
        if ($a === null && $b === null) return false;
        if ($a === null || $b === null) return true;
        return (string) $a !== (string) $b;
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

        // Format options
        $parsedPhysicalVersion = $this->parsePhysicalVersion();
        $parsedDLC = $this->parseDLC();
        $parsedDemo = $this->parseDemo();
        if (!is_null($parsedPhysicalVersion)) {
            $this->dataSourceParsed->has_physical_version = $parsedPhysicalVersion;
        }
        if (!is_null($parsedDLC)) {
            $this->dataSourceParsed->has_dlc = $parsedDLC;
        }
        if (!is_null($parsedDemo)) {
            $this->dataSourceParsed->has_demo = $parsedDemo;
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
            $pubReplacementsBlanks = [
                'Co.,Ltd',
                'Co., Ltd.',
                'Co. Ltd',
                'CO.,LTD.',
                'CO., LTD',
                ' Ltd.',
                ' Ltd',
                ' LTD.',
                ' LTD',
                ' LIMITED',
                ', LLC',
                ' LLC',
                'LLC',
                ', Incorporated',
                ', Inc.',
                ', Inc',
                ', inc',
                ' Inc.',
                '®',
                ', S.L.',
                ' S.L',
                ' Sp. z.o.o Sp.K',
                ' Sp. z.o.o',
                ' Sp.z.o.o',
                ' sp. z.o.o',
                ' S.r.l.',
                ' S.R.L',
                ' srl',
                ', s.r.o.',
                ' s.r.o',
                ' G.K',
                ' B.V',
                ' d.o.o',
                ' Pty',
                ' Pty Ltd',
                ' (Pty.)',
                ' PTY',
                ' FK AB',
                ' GmbH',
            ];

            foreach ($pubReplacementsBlanks as $pubReplacement) {
                $parsedPublishers = str_replace($pubReplacement, '', $parsedPublishers);
            }

            // Consistency
            $parsedPublishers = str_replace(' ENTMT', ' Entertainment', $parsedPublishers);
            $parsedPublishers = str_replace(' Ent.', ' Entertainment', $parsedPublishers);
            //$parsedPublishers = str_replace(' Ent', ' Entertainment', $parsedPublishers);
            $parsedPublishers = trim($parsedPublishers);
            $parsedPublishers = rtrim($parsedPublishers, ".");

            if (is_array($parsedPublishers)) {
                $publisherArray = $parsedPublishers;
            } elseif (str_contains($parsedPublishers, ",")) {
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

    public function parsePhysicalVersion()
    {
        if (!array_key_exists('physical_version_b', $this->rawJsonData)) {
            return null;
        }

        return $this->rawJsonData['physical_version_b'];
    }

    public function parseDLC()
    {
        if (!array_key_exists('dlc_shown_b', $this->rawJsonData)) {
            return null;
        }

        return $this->rawJsonData['dlc_shown_b'];
    }

    public function parseDemo()
    {
        if (!array_key_exists('demo_availability', $this->rawJsonData)) {
            return null;
        }

        return $this->rawJsonData['demo_availability'];
    }
}