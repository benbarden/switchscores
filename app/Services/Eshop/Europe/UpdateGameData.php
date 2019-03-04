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
}