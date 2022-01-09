<?php

namespace App\Construction\GameImportRule;

use App\Game;
use App\Models\GameImportRuleEshop;

class EshopBuilder
{
    /**
     * @var Game
     */
    private $game;

    /**
     * @var \App\Models\GameImportRuleEshop
     */
    private $gameImportRule;

    public function __construct()
    {
        $this->reset();
    }

    public function reset(): void
    {
        $this->game = new Game;
        $this->gameImportRule = new GameImportRuleEshop;
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

    public function getGameImportRule(): GameImportRuleEshop
    {
        return $this->gameImportRule;
    }

    public function setGameImportRule(GameImportRuleEshop $gameImportRule): void
    {
        $this->gameImportRule = $gameImportRule;
    }

    public function setIgnorePublishers($ignorePublishers): void
    {
        $this->gameImportRule->ignore_publishers = $ignorePublishers;
    }

    public function setIgnoreEuropeDates($ignoreEuropeDates): void
    {
        $this->gameImportRule->ignore_europe_dates = $ignoreEuropeDates;
    }

    public function setIgnorePrice($ignorePrice): void
    {
        $this->gameImportRule->ignore_price = $ignorePrice;
    }

    public function setIgnorePlayers($ignorePlayers): void
    {
        $this->gameImportRule->ignore_players = $ignorePlayers;
    }

    public function setIgnoreGenres($ignoreGenres): void
    {
        $this->gameImportRule->ignore_genres = $ignoreGenres;
    }
}