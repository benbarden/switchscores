<?php

namespace App\Services\DataSources\Wikipedia;

use App\DataSourceRaw;
use App\DataSourceParsed;
use App\Services\GameTitleHashService;

class Parser
{
    /**
     * @var DataSourceParsed
     */
    private $dataSourceParsed;

    /**
     * @var array
     */
    private $rawJsonData;

    /**
     * @var boolean
     */
    private $hasSufficientDataToSave;

    public function getParsedItem()
    {
        return $this->dataSourceParsed;
    }

    public function __construct(DataSourceRaw $dataSourceRaw)
    {
        $dataSourceParsed = new DataSourceParsed();

        $dataSourceParsed->source_id = $dataSourceRaw->source_id;
        $dataSourceParsed->title = $dataSourceRaw->title;

        $this->dataSourceParsed = $dataSourceParsed;
        $this->rawJsonData = json_decode($dataSourceRaw->source_data_json, true);
    }

    /**
     * @return boolean
     */
    public function isOkToSave()
    {
        return $this->hasSufficientDataToSave;
    }

    /**
     * @return DataSourceParsed
     */
    public function parseItem()
    {
        // Developers, Publishers
        $this->dataSourceParsed->developers = $this->rawJsonData['developers'];
        $this->dataSourceParsed->publishers = $this->rawJsonData['publishers'];

        // Release dates
        $releaseDateEU = $this->parseReleaseDateEU();
        $releaseDateUS = $this->parseReleaseDateUS();
        $releaseDateJP = $this->parseReleaseDateJP();
        if (!is_null($releaseDateEU)) {
            $this->dataSourceParsed->release_date_eu = $releaseDateEU;
        }
        if (!is_null($releaseDateUS)) {
            $this->dataSourceParsed->release_date_us = $releaseDateUS;
        }
        if (!is_null($releaseDateJP)) {
            $this->dataSourceParsed->release_date_jp = $releaseDateJP;
        }

        // Rules for not saving
        if (is_null($releaseDateEU) && is_null($releaseDateUS)) {
            $this->hasSufficientDataToSave = false;
        } else {
            $this->hasSufficientDataToSave = true;
        }

        return $this->dataSourceParsed;
    }

    public function linkToGameId(GameTitleHashService $serviceGameTitleHash)
    {
        $title = $this->dataSourceParsed->title;

        // See if we can locate the game
        $titleHash = $serviceGameTitleHash->generateHash($title);
        $gameTitleHash = $serviceGameTitleHash->getByHash($titleHash);
        if ($gameTitleHash) {
            $gameId = $gameTitleHash->game_id;
        } else {
            $gameId = null;
        }

        $this->dataSourceParsed->game_id = $gameId;
    }

    public function parseReleaseDateEU()
    {
        if (!array_key_exists('release_date_eu', $this->rawJsonData)) {
            return null;
        }

        $parsedDate = $this->rawJsonData['release_date_eu'];

        return $parsedDate;
    }

    public function parseReleaseDateUS()
    {
        if (!array_key_exists('release_date_us', $this->rawJsonData)) {
            return null;
        }

        $parsedDate = $this->rawJsonData['release_date_us'];

        return $parsedDate;
    }

    public function parseReleaseDateJP()
    {
        if (!array_key_exists('release_date_jp', $this->rawJsonData)) {
            return null;
        }

        $parsedDate = $this->rawJsonData['release_date_jp'];

        return $parsedDate;
    }
}