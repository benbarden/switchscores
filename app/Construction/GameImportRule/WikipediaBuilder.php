<?php

namespace App\Construction\GameImportRule;

use App\Game;
use App\GameImportRuleWikipedia;

class WikipediaBuilder
{
    /**
     * @var Game
     */
    private $game;

    /**
     * @var GameImportRuleWikipedia
     */
    private $gameImportRule;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->game = new Game;
        $this->gameImportRule = new GameImportRuleWikipedia;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function setGameId($gameId): void
    {
        $this->gameImportRule->game_id = $gameId;
    }

    public function getGameId(): int
    {
        return $this->gameImportRule->game_id;
    }

    public function getGameImportRule(): GameImportRuleWikipedia
    {
        return $this->gameImportRule;
    }

    public function setGameImportRule(GameImportRuleWikipedia $gameImportRule): void
    {
        $this->gameImportRule = $gameImportRule;
    }

    public function setIgnoreDevelopers($ignoreDevelopers): void
    {
        $this->gameImportRule->ignore_developers = $ignoreDevelopers;
    }

    public function setIgnorePublishers($ignorePublishers): void
    {
        $this->gameImportRule->ignore_publishers = $ignorePublishers;
    }

    public function setIgnoreEuropeDates($ignoreEuropeDates): void
    {
        $this->gameImportRule->ignore_europe_dates = $ignoreEuropeDates;
    }

    public function setIgnoreUSDates($ignoreUSDates): void
    {
        $this->gameImportRule->ignore_us_dates = $ignoreUSDates;
    }

    public function setIgnoreJPDates($ignoreJPDates): void
    {
        $this->gameImportRule->ignore_jp_dates = $ignoreJPDates;
    }

    public function setIgnoreGenres($ignoreGenres): void
    {
        $this->gameImportRule->ignore_genres = $ignoreGenres;
    }
}
