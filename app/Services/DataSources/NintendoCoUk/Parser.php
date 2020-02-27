<?php


namespace App\Services\DataSources\NintendoCoUk;

use App\DataSourceRaw;
use App\DataSourceParsed;

class Parser
{
    /**
     * @param $dataSourceRaw
     * @return DataSourceParsed
     */
    public function parseItem($dataSourceRaw)
    {
        $dataSourceParsed = new DataSourceParsed();

        $dataSourceParsed->source_id = $dataSourceRaw->source_id;
        $dataSourceParsed->title = $dataSourceRaw->title;

        $rawJsonData = json_decode($dataSourceRaw->source_data_json, true);

        // Price
        $parsedPrice = $this->parsePrice($rawJsonData);
        if (!is_null($parsedPrice)) {
            $dataSourceParsed->price = $parsedPrice;
        }

        // Release date
        $parsedReleaseDate = $this->parseReleaseDate($rawJsonData);
        if (!is_null($parsedReleaseDate)) {
            $dataSourceParsed->release_date = $parsedReleaseDate;
        }

        // Publishers
        $dataSourceParsed->publishers = $rawJsonData['publisher'];

        return $dataSourceParsed;
    }

    public function parsePrice($rawJsonData)
    {
        $parsedPrice = null;

        if (array_key_exists('price_regular_f', $rawJsonData)) {
            $rawPriceRegularF = $rawJsonData['price_regular_f'];
        } else {
            $rawPriceRegularF = null;
        }
        if (array_key_exists('price_lowest_f', $rawJsonData)) {
            $rawPriceLowestF = $rawJsonData['price_lowest_f'];
        } else {
            $rawPriceLowestF = null;
        }
        if (array_key_exists('price_discount_percentage_f', $rawJsonData)) {
            $rawPriceDiscountPercentageF = $rawJsonData['price_discount_percentage_f'];
        } else {
            $rawPriceDiscountPercentageF = '0.0';
        }
        if (($rawPriceRegularF != null) && ($rawPriceRegularF > 0)) {
            $parsedPrice = $rawPriceRegularF;
        } elseif (($rawPriceLowestF != null) && ($rawPriceLowestF > 0) && ($rawPriceDiscountPercentageF == '0.0')) {
            $parsedPrice = $rawPriceLowestF;
        }

        return $parsedPrice;
    }

    public function parseReleaseDate($rawJsonData)
    {
        $parsedReleaseDate = null;

        if (!array_key_exists('pretty_date_s', $rawJsonData)) {
            return null;
        }
        $rawReleaseDate = $rawJsonData['pretty_date_s'];

        try {
            $eshopReleaseDateObj = \DateTime::createFromFormat('d/m/Y', $rawReleaseDate);
            $parsedReleaseDate = $eshopReleaseDateObj->format('Y-m-d');
        } catch (\Throwable $e) {
            // Date error
        }

        return $parsedReleaseDate;
    }
}