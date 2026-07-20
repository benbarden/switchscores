<?php

namespace App\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImportRuleEshop;

class UpdateGame
{
    /**
     * @var \App\Models\Game
     */
    private $game;

    /**
     * @var \App\Models\DataSourceParsed
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
        $priceDiscounted = $this->dsParsedItem->price_discounted;
        $priceDiscountPc = $this->dsParsedItem->price_discount_pc;

        // These guards reject an untrustworthy standard price. They used to `return false`,
        // which also skipped the discount fields below, so a game whose API price was missing
        // or zero kept an ended sale's discount indefinitely. Rejecting the standard price now
        // clears the discount instead of leaving it: if the payload's price can't be trusted,
        // its discount can't either, and a stale discount is worse than none.
        $standardPriceIsUsable = true;
        if (is_null($priceStandard)) {
            $standardPriceIsUsable = false;
        } elseif ($priceStandard < 0) {
            $standardPriceIsUsable = false;
        } elseif ($priceStandard == 0 && $this->game->price_eshop > 0) {
            // Switch 2 games can return 0.00 from the API even when they have a real price;
            // don't overwrite an existing price with zero
            $standardPriceIsUsable = false;
        }

        if ($standardPriceIsUsable) {
            if ($this->game->price_eshop != $priceStandard) {
                $this->game->price_eshop = $priceStandard;
            }
        } else {
            // Don't trust this payload's discount either — clear rather than carry it forward.
            $priceDiscounted = null;
            $priceDiscountPc = null;
        }

        if ($this->game->price_eshop_discounted != $priceDiscounted) {
            $this->game->price_eshop_discounted = $priceDiscounted;
        }
        if ($this->game->price_eshop_discount_pc != $priceDiscountPc) {
            $this->game->price_eshop_discount_pc = $priceDiscountPc;
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

    public function updatePhysicalVersion()
    {
        if ($this->game->format_physical != null) return false;

        if ($this->dsParsedItem->has_physical_version == 1) {
            $this->game->format_physical = Game::FORMAT_AVAILABLE;
        } else {
            $this->game->format_physical = Game::FORMAT_NOT_AVAILABLE;
        }
    }

    public function updateDLC()
    {
        if ($this->game->format_dlc != null) return false;

        if ($this->dsParsedItem->has_dlc == 1) {
            $this->game->format_dlc = Game::FORMAT_AVAILABLE;
        } else {
            $this->game->format_dlc = Game::FORMAT_NOT_AVAILABLE;
        }
    }

    public function updateDemo()
    {
        if ($this->game->format_demo != null) return false;

        if ($this->dsParsedItem->has_demo == 1) {
            $this->game->format_demo = Game::FORMAT_AVAILABLE;
        } else {
            $this->game->format_demo = Game::FORMAT_NOT_AVAILABLE;
        }
    }
}