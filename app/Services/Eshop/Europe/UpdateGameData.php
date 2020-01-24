<?php

namespace App\Services\Eshop\Europe;

use Illuminate\Support\Collection;

use App\Game;
use App\GameImportRuleEshop;
use App\EshopEuropeGame;
use App\EshopEuropeAlert;

use App\Services\GenreService;
use App\Services\GameGenreService;

class UpdateGameData
{
    /**
     * @var EshopEuropeGame
     */
    private $eshopItem;

    /**
     * @var EshopEuropeAlert
     */
    private $eshopAlert;

    /**
     * @var Game
     */
    private $game;

    /**
     * @var GameImportRuleEshop
     */
    private $gameImportRuleEshop;

    /**
     * @var boolean
     */
    private $hasGameChanged;

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

    /**
     * @return EshopEuropeGame
     */
    public function getEshopItem()
    {
        return $this->eshopItem;
    }

    public function setEshopItem($eshopItem)
    {
        $this->eshopItem = $eshopItem;
    }

    /**
     * @return EshopEuropeAlert
     */
    public function getEshopAlert()
    {
        return $this->eshopAlert;
    }

    public function setEshopAlert($typeId, $errorMsg, $currentData, $newData)
    {
        $eshopAlert = new EshopEuropeAlert();
        $eshopAlert->game_id = $this->game->id;
        $eshopAlert->type = $typeId;
        $eshopAlert->error_message = $errorMsg;
        $eshopAlert->current_data = $currentData;
        $eshopAlert->new_data = $newData;
        $this->eshopAlert = $eshopAlert;
    }

    public function setEshopAlertError($errorMsg, $currentData, $newData)
    {
        $typeId = EshopEuropeAlert::TYPE_ERROR;
        $this->setEshopAlert($typeId, $errorMsg, $currentData, $newData);
    }

    public function setEshopAlertWarning($errorMsg, $currentData, $newData)
    {
        $typeId = EshopEuropeAlert::TYPE_WARNING;
        $this->setEshopAlert($typeId, $errorMsg, $currentData, $newData);
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

    public function setGameImportRule(GameImportRuleEshop $gameImportRule)
    {
        $this->gameImportRuleEshop = $gameImportRule;
    }

    public function getGameImportRule(): GameImportRuleEshop
    {
        return $this->gameImportRuleEshop;
    }

    public function hasGameChanged()
    {
        return $this->hasGameChanged;
    }

    public function resetLogMessages()
    {
        $this->logMessageInfo = null;
        $this->logMessageWarning = null;
        $this->logMessageError = null;
        $this->eshopAlert = null;
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
        if ($this->gameImportRuleEshop) {
            if ($this->gameImportRuleEshop->shouldIgnorePlayers()) return false;
        }

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
            // Set alert
            $this->setEshopAlertWarning('Different player info', $gamePlayers, $expectedPlayers);
        } else {
            // Same value, nothing to do
        }
    }

    public function updatePublisher()
    {
        if ($this->gameImportRuleEshop) {
            if ($this->gameImportRuleEshop->shouldIgnorePublishers()) return false;
        }

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
                // Set alert
                $this->setEshopAlertWarning('Different publisher', $this->game->publisher, $eshopPublisher);
            } else {
                // Same value, nothing to do
            }
        }
    }

    public function updatePrice()
    {
        if ($this->gameImportRuleEshop) {
            if ($this->gameImportRuleEshop->shouldIgnorePrice()) return false;
        }

        $gameTitle = $this->game->title;
        $eshopPriceLowest = $this->eshopItem->price_lowest_f;
        $eshopPriceDiscount = $this->eshopItem->price_discount_percentage_f;
        // New fields
        $eshopPriceRegular = $this->eshopItem->price_regular_f;
        $eshopPriceDiscounted = $this->eshopItem->price_discounted_f;

        if ($eshopPriceRegular > 0) {

            // Use new eShop field
            if ($this->game->price_eshop == null) {

                // Not set, so let's update it
                $this->logMessageInfo = $gameTitle.' - no price set. '.
                    'Expected: '.$eshopPriceRegular.' - Updating.';
                $this->game->price_eshop = $eshopPriceRegular;
                $this->hasGameChanged = true;

            } elseif ($this->game->price_eshop != $eshopPriceRegular) {

                // Different
                $this->logMessageInfo = $gameTitle.' - different price. '.
                    'Game data: '.$this->game->price_eshop.' - '.
                    'Expected: '.$eshopPriceRegular.' - Updating.';
                $this->game->price_eshop = $eshopPriceRegular;
                $this->hasGameChanged = true;

            }

        } elseif ($eshopPriceLowest < 0) {

            // Skip negative prices. This is an error in the API!
            $this->logMessageError = $gameTitle . ' - Price is negative - skipping. ' .
                'Price: ' . $eshopPriceLowest;

            // Set alert
            $this->setEshopAlertError('Price is negative', $this->game->price_eshop, $eshopPriceLowest);

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
        if ($this->gameImportRuleEshop) {
            if ($this->gameImportRuleEshop->shouldIgnoreEuropeDates()) return false;
        }

        $nowDate = new \DateTime('now');

        $gameTitle = $this->game->title;
        $eshopReleaseDateRaw = $this->eshopItem->pretty_date_s;

        // *** FIELD UPDATES:
        // European release date
        // Check for bad dates
        $badDatesArray = [
            'TBD',
            '2019',
            '2020',
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

            // Set alert
            $this->setEshopAlertError('Date error', $this->game->eu_release_date, $eshopReleaseDateRaw);

            return;
        }

        if (!$isBadDate) {

            if ($this->game->eu_release_date == null) {

                // Not set
                $this->logMessageInfo = $gameTitle.' - no release date. '.
                    'eShop data: '.$eshopReleaseDate.' - Updating.';

                $this->game->eu_release_date = $eshopReleaseDate;

                $releaseYearObj = new \DateTime($eshopReleaseDate);
                $releaseYear = $releaseYearObj->format('Y');

                $this->game->release_year = $releaseYear;
                if ($eshopReleaseDateObj > $nowDate) {
                    $this->game->eu_is_released = 0;
                } else {
                    $dateNow = new \DateTime('now');
                    $this->game->eu_is_released = 1;
                    $this->game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
                }

                $this->hasGameChanged = true;

            } elseif ($this->game->eu_release_date != $eshopReleaseDate) {

                // Different
                $this->logMessageWarning = $gameTitle.' - different release date. '.
                    'Game data: '.$this->game->eu_release_date.' - '.
                    'eShop data: '.$eshopReleaseDate;

                // Set alert
                $this->setEshopAlertWarning('Different release date', $this->game->eu_release_date, $eshopReleaseDate);

            } else {

                // Same value, nothing to do

            }

        }
    }

    public function updateGenres()
    {
        if ($this->gameImportRuleEshop) {
            if ($this->gameImportRuleEshop->shouldIgnoreGenres()) return false;
        }

        $serviceGenre = new GenreService();
        $serviceGameGenre = new GameGenreService();

        $gameTitle = $this->game->title;
        $gameId = $this->game->id;
        $gameGenres = $serviceGameGenre->getByGame($gameId);

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

            // Set alert
            $this->setEshopAlertWarning('Different genre count', implode(',', $gameGenresArray), implode(',', $eshopGenres));

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
                // Set alert
                $this->setEshopAlertError('Genre not found', null, $genreItem);
                continue;
            }
            $genreId = $genreItem->id;
            $serviceGameGenre->create($gameId, $genreId);
        }
    }
}