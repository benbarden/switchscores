<?php

namespace App\Services\DataSources\NintendoCoUk;

use App\Game;
use App\GameImportRuleEshop;
use App\DataSourceParsed;

use App\Services\GenreService;
use App\Services\GameGenreService;

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
     * @var GameImportRuleEshop
     */
    private $gameImportRule;

    public function __construct(Game $game, DataSourceParsed $dsParsedItem, GameImportRuleEshop $gameImportRule = null)
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

    public function updatePrice()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnorePrice()) return false;
        }

        $priceStandard = $this->dsParsedItem->price_standard;

        if (is_null($priceStandard)) return false;
        if ($priceStandard < 0) return false;

        if ($this->game->price_eshop != $priceStandard) {
            $this->game->price_eshop = $priceStandard;
        }
    }

    public function updateReleaseDate()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnoreEuropeDates()) return false;
        }

        $releaseDateEu = $this->dsParsedItem->release_date_eu;

        if (is_null($releaseDateEu)) return false;

        // Ignore games that already have dates
        if ($this->game->eu_release_date != null) return false;

        $this->game->eu_release_date = $releaseDateEu;

        $releaseDateObj = new \DateTime($releaseDateEu);
        $releaseYear = $releaseDateObj->format('Y');

        $nowDate = new \DateTime('now');

        $this->game->release_year = $releaseYear;

        if ($releaseDateObj > $nowDate) {
            $this->game->eu_is_released = 0;
        } else {
            $dateNow = new \DateTime('now');
            $this->game->eu_is_released = 1;
            $this->game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
        }
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

    public function updatePlayers()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnorePlayers()) return false;
        }

        if ($this->game->players != null) return false;

        $this->game->players = $this->dsParsedItem->players;
    }

    public function updateGenres()
    {
        if ($this->gameImportRule) {
            if ($this->gameImportRule->shouldIgnoreGenres()) return false;
        }

        $serviceGenre = new GenreService();
        $serviceGameGenre = new GameGenreService();

        $gameId = $this->game->id;
        $gameGenres = $serviceGameGenre->getByGame($gameId);

        $dsGenres = $this->dsParsedItem->genres_json;
        if ($dsGenres == null) return false;

        $eshopGenres = json_decode($dsGenres);

        $gameGenresArray = [];
        foreach ($gameGenres as $gameGenre) {
            $gameGenresArray[] = $gameGenre->genre->genre;
        }

        if (count($eshopGenres) == 0) return false;

        if (count($gameGenres) != 0) return false;

        foreach ($eshopGenres as $eshopGenre) {
            $genreItem = $serviceGenre->getByGenreTitle($eshopGenre);
            if (!$genreItem) continue;
            $genreId = $genreItem->id;
            $serviceGameGenre->create($gameId, $genreId);
        }
    }
}