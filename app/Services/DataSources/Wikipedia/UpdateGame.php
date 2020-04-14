<?php

namespace App\Services\DataSources\Wikipedia;

use App\Game;
use App\GameImportRuleWikipedia;
use App\DataSourceParsed;

class UpdateGame
{
    /**
     * @var Game
     */
    private $game;

    /**
     * @var DataSourceParsed
     */
    private $dsParsedItem;

    /**
     * @var GameImportRuleWikipedia
     */
    private $gameImportRule;

    public function __construct(Game $game, DataSourceParsed $dsParsedItem, GameImportRuleWikipedia $gameImportRule = null)
    {
        $this->game = $game;
        $this->dsParsedItem = $dsParsedItem;
        if ($gameImportRule) {
            $this->gameImportRule = $gameImportRule;
        }
    }

    public function getGame()
    {
        return $this->game;
    }

    public function updateDevelopers()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnoreDevelopers()) return false;
        }

        if ($this->game->gameDevelopers()->count() > 0) return false;

        if ($this->game->developer != null) return false;

        $this->game->developer = $this->dsParsedItem->developer;
    }

    public function updatePublishers()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnorePublishers()) return false;
        }

        if ($this->game->gamePublishers()->count() > 0) return false;

        if ($this->game->publisher != null) return false;

        $this->game->publisher = $this->dsParsedItem->publishers;
    }

    public function updateReleaseDateUS()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnoreUSDates()) return false;
        }

        $releaseDateUS = $this->dsParsedItem->release_date_us;

        if (is_null($releaseDateUS)) return false;

        if ($this->game->us_release_date != null) return false;

        $this->game->us_release_date = $releaseDateUS;
    }

    public function updateReleaseDateJP()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnoreJPDates()) return false;
        }

        $releaseDateJP = $this->dsParsedItem->release_date_jp;

        if (is_null($releaseDateJP)) return false;

        if ($this->game->jp_release_date != null) return false;

        $this->game->jp_release_date = $releaseDateJP;
    }
}