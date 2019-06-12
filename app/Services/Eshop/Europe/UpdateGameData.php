<?php

namespace App\Services\Eshop\Europe;

use Illuminate\Support\Collection;

use App\EshopEuropeGame;
use App\Game;
use App\GameReleaseDate;

use App\Services\GenreService;
use App\Services\GameGenreService;

class UpdateGameData
{
    /**
     * @var EshopEuropeGame
     */
    private $eshopItem;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var GameReleaseDate
     */
    private $gameReleaseDate;

    /**
     * @var Collection
     */
    private $gameGenres;

    /**
     * @var boolean
     */
    private $hasGameChanged;

    /**
     * @var boolean
     */
    private $hasGameReleaseDateChanged;

    /**
     * @var string
     */
    private $logMessageInfo;

    /**
     * @var string
     */
    private $logMessageWarning;

    /**
     * @var string
     */
    private $logMessageError;

    public function getEshopItem()
    {
        return $this->eshopItem;
    }

    public function setEshopItem($eshopItem)
    {
        $this->eshopItem = $eshopItem;
    }

    public function getGame()
    {
        return $this->game;
    }

    public function setGame($game)
    {
        $this->game = $game;
        // Reset gameChanged when passing in a new model
        $this->hasGameChanged = false;
    }

    public function getGameReleaseDate()
    {
        return $this->gameReleaseDate;
    }

    public function setGameReleaseDate($gameReleaseDate)
    {
        $this->gameReleaseDate = $gameReleaseDate;
        $this->hasGameReleaseDateChanged = false;
    }

    public function getGameGenres()
    {
        return $this->gameGenres;
    }

    public function setGameGenres($gameGenres)
    {
        $this->gameGenres = $gameGenres;
    }

    public function hasGameChanged()
    {
        return $this->hasGameChanged;
    }

    public function hasGameReleaseDateChanged()
    {
        return $this->hasGameReleaseDateChanged;
    }

    public function resetLogMessages()
    {
        $this->logMessageInfo = null;
        $this->logMessageWarning = null;
        $this->logMessageError = null;
    }

    public function getLogMessageInfo()
    {
        return $this->logMessageInfo;
    }

    public function getLogMessageWarning()
    {
        return $this->logMessageWarning;
    }

    public function getLogMessageError()
    {
        return $this->logMessageError;
    }

    /**
     * Helper method for getting any log message, if set, in order of precedence.
     * Priority is as follows:
     *  - error
     *  - warning
     *  - info
     * If no messages are set, null is returned.
     * @return null|string
     */
    public function getLogMessage()
    {
        if ($this->logMessageError) {
            return $this->logMessageError;
        } elseif ($this->logMessageWarning) {
            return $this->logMessageWarning;
        } elseif ($this->logMessageInfo) {
            return $this->logMessageInfo;
        } else {
            return null;
        }
    }

    public function updateNoOfPlayers()
    {
        $gameTitle = $this->game->title;
        $gamePlayers = $this->game->players;
        $eshopPlayersFrom = $this->eshopItem->players_from;
        $eshopPlayersTo = $this->eshopItem->players_to;

        if (!$eshopPlayersFrom) {
            $eshopPlayersFrom = "1";
        }
        if ($eshopPlayersTo == 1) {
            $expectedPlayers = "1";
        } elseif (($eshopPlayersTo == "") || ($eshopPlayersTo == null)) {
            $expectedPlayers = "";
        } else {
            $expectedPlayers = $eshopPlayersFrom."-".$eshopPlayersTo;
        }

        if ($gamePlayers == null) {
            // Check there's actually something to update
            if ($expectedPlayers != "") {
                // Not set, so let's update it
                $this->logMessageInfo = $gameTitle.' - no player info. '.
                    'Expected: '.$expectedPlayers.' - Updating.';
                $this->game->players = $expectedPlayers;
                $this->hasGameChanged = true;
            }
        } elseif ($gamePlayers != $expectedPlayers) {
            // Different
            $this->logMessageWarning = $gameTitle.' - different player info. '.
                'Game data: '.$gamePlayers.' - '.
                'Expected: '.$expectedPlayers;
        } else {
            // Same value, nothing to do
        }
    }

    public function updatePublisher()
    {
        $gameTitle = $this->game->title;
        $eshopPublisher = $this->eshopItem->publisher;

        if (strtoupper($eshopPublisher) == $eshopPublisher) {
            // It's all uppercase, so make it title case
            $eshopPublisher = ucwords(strtolower($eshopPublisher));
        } else {
            // Leave it alone
        }

        if ($this->game->gamePublishers()->count() == 0) {
            // Only proceed if new publisher db entries do not exist
            if ($this->game->publisher == null) {
                // Not set, so let's update it
                $this->logMessageInfo = $gameTitle.' - no publisher. '.
                    'Expected: '.$eshopPublisher.' - Updating.';
                $this->game->publisher = $eshopPublisher;
                $this->hasGameChanged = true;
            } elseif ($this->game->publisher != $eshopPublisher) {
                // Different
                $this->logMessageWarning = $gameTitle.' - different publisher. '.
                    'Game data: '.$this->game->publisher.' - '.
                    'Expected: '.$eshopPublisher;
            } else {
                // Same value, nothing to do
            }
        }
    }

    public function updatePrice()
    {
        $gameTitle = $this->game->title;
        $eshopPriceLowest = $this->eshopItem->price_lowest_f;
        $eshopPriceDiscount = $this->eshopItem->price_discount_percentage_f;

        if ($eshopPriceLowest < 0) {

            // Skip negative prices. This is an error in the API!
            $this->logMessageError = $gameTitle.' - Price is negative - skipping. '.
                'Price: '.$eshopPriceLowest;

        } elseif ($eshopPriceDiscount != '0.0') {

            // Skip discounts. For most games, we'll do this silently so as to save log noise.
            // If there's no price set, we'll mention it.
            if ($this->game->price_eshop == null) {
                $this->logMessageInfo = $gameTitle.' - Price is discounted - skipping. '.
                    'Price: '.$eshopPriceLowest.'; Discount: '.$eshopPriceDiscount;
            }

        } elseif ($this->game->price_eshop == null) {

            // Not set, so let's update it
            $this->logMessageInfo = $gameTitle.' - no price set. '.
                'Expected: '.$eshopPriceLowest.' - Updating.';
            $this->game->price_eshop = $eshopPriceLowest;
            $this->hasGameChanged = true;

        } elseif ($this->game->price_eshop != $eshopPriceLowest) {

            // Different
            $this->logMessageInfo = $gameTitle.' - different price. '.
                'Game data: '.$this->game->price_eshop.' - '.
                'Expected: '.$eshopPriceLowest.' - Updating.';
            $this->game->price_eshop = $eshopPriceLowest;
            $this->hasGameChanged = true;

        } else {

            // Same value, nothing to do

        }
    }

    public function updateReleaseDate()
    {
        $nowDate = new \DateTime('now');

        $gameTitle = $this->game->title;
        $eshopReleaseDateRaw = $this->eshopItem->pretty_date_s;

        // *** FIELD UPDATES:
        // European release date
        // Check for bad dates
        $badDatesArray = [
            'TBD',
            '2019',
            'Spring 2019',
            'January 2019',
        ];
        try {
            if (in_array($eshopReleaseDateRaw, $badDatesArray)) {
                $isBadDate = true;
            } else {
                $isBadDate = false;
                $eshopReleaseDateObj = \DateTime::createFromFormat('d/m/Y', $eshopReleaseDateRaw);
                $eshopReleaseDate = $eshopReleaseDateObj->format('Y-m-d');
            }
        } catch (\Throwable $e) {
            $this->logMessageError = 'ERROR: ['.$eshopReleaseDateRaw.'] - '.$e->getMessage();
            return;
        }

        if (!$isBadDate) {

            if ($this->gameReleaseDate->release_date == null) {

                // Not set
                $this->logMessageInfo = $gameTitle.' - no release date. '.
                    'eShop data: '.$eshopReleaseDate.' - Updating.';

                $this->gameReleaseDate->release_date = $eshopReleaseDate;
                $this->gameReleaseDate->upcoming_date = $eshopReleaseDate;

                if ($eshopReleaseDateObj > $nowDate) {
                    $this->gameReleaseDate->is_released = 0;
                } else {
                    $this->gameReleaseDate->is_released = 1;
                }

                $this->hasGameReleaseDateChanged = true;
                //$gameReleaseDate->save();

            } elseif ($this->gameReleaseDate->release_date != $eshopReleaseDate) {

                // Different
                $this->logMessageWarning = $gameTitle.' - different release date. '.
                    'Game data: '.$this->gameReleaseDate->release_date.' - '.
                    'eShop data: '.$eshopReleaseDate;

            } else {

                // Same value, nothing to do

            }

        }
    }

    public function updateGenres()
    {
        $serviceGenre = new GenreService();
        $serviceGameGenre = new GameGenreService();

        $gameTitle = $this->game->title;
        $gameId = $this->game->id;
        $gameGenres = $this->gameGenres;

        $eshopGenreList = $this->eshopItem->pretty_game_categories_txt;

        if (!$eshopGenreList) return;

        $eshopGenres = json_decode($eshopGenreList);
        $gameGenresArray = [];
        foreach ($gameGenres as $gameGenre) {
            $gameGenresArray[] = $gameGenre->genre->genre;
        }
        //$this->logMessageInfo = $gameTitle.' - Found '.count($eshopGenres).' genre(s) in eShop data';

        $okToAddGenres = false;

        if (count($eshopGenres) == 0) {

            $this->logMessageInfo = $gameTitle.' - No eShop genres. Skipping';
            $okToAddGenres = false;

        } elseif (count($gameGenres) == 0) {

            $this->logMessageInfo = $gameTitle.' - No existing genres. Adding new genres.';
            $okToAddGenres = true;

        } elseif (count($gameGenres) != count($eshopGenres)) {

            $this->logMessageWarning = $gameTitle.' - '.
                'Game has '.count($gameGenres).' ['.implode(',', $gameGenresArray).']; '.
                'eShop has '.count($eshopGenres).' ['.implode(',', $eshopGenres).']. '.
                'Check for differences.';
            $okToAddGenres = false;

        } else {

            $okToAddGenres = false;

        }

        if (!$okToAddGenres) return;

        if (count($gameGenres) > 0) {
            $serviceGameGenre->deleteGameGenres($gameId);
        }
        foreach ($eshopGenres as $eshopGenre) {
            $genreItem = $serviceGenre->getByGenreTitle($eshopGenre);
            if (!$genreItem) {
                $this->logMessageError = 'Genre not found: '.$genreItem.'; skipping';
                continue;
            }
            $genreId = $genreItem->id;
            $serviceGameGenre->create($gameId, $genreId);
        }
    }
}