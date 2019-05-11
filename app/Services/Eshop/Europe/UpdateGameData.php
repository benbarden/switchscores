<?php

namespace App\Services\Eshop\Europe;

use App\EshopEuropeGame;
use App\Game;

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

    public function hasGameChanged()
    {
        return $this->hasGameChanged;
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

    public function updateNintendoPageUrl()
    {
        $gameTitle = $this->game->title;
        $eshopUrl = $this->eshopItem->url;

        if ($this->game->nintendo_page_url == null) {
            // No URL set, so let's update it
            $this->logMessageInfo = $gameTitle.' - no existing nintendo_page_url. Updating.';
            $this->game->nintendo_page_url = $eshopUrl;
            $this->hasGameChanged = true;
        } elseif ($this->game->nintendo_page_url != $eshopUrl) {
            // URL set to something else
        } else {
            // It's the same, so nothing to do
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
            // Not set, so let's update it
            $this->logMessageInfo = $gameTitle.' - no player info. '.
                'Expected: '.$expectedPlayers.' - Updating.';
            $this->game->players = $expectedPlayers;
            $this->hasGameChanged = true;
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
}