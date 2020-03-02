<?php


namespace App\Services\HtmlLoader\Wikipedia;

use Illuminate\Support\Collection;

use App\CrawlerWikipediaGamesListSource;
use App\FeedItemGame;
use App\Game;
use App\GameImportRuleWikipedia;
use Carbon\Carbon;

class Importer
{
    /**
     * @var CrawlerWikipediaGamesListSource
     */
    private $crawlerModel;

    public function setCrawlerModel(CrawlerWikipediaGamesListSource $crawlerModel)
    {
        $this->crawlerModel = $crawlerModel;
    }

    /**
     * @return FeedItemGame
     */
    public function generateFeedModel()
    {
        $feedItemGame = new FeedItemGame();

        //$feedItemGame->game_id = null; // To be filled in later
        $feedItemGame->source = 'Wikipedia';
        $feedItemGame->item_title = $this->crawlerModel->title;
        $feedItemGame->item_genre = $this->crawlerModel->genres;
        $feedItemGame->item_developers = $this->crawlerModel->developers;
        $feedItemGame->item_publishers = $this->crawlerModel->publishers;
        $feedItemGame->release_date_eu = $this->crawlerModel->release_date_eu;
        $feedItemGame->release_date_us = $this->crawlerModel->release_date_us;
        $feedItemGame->release_date_jp = $this->crawlerModel->release_date_jp;
        $feedItemGame->setStatusPending();

        return $feedItemGame;
    }

    public function getGameModifiedFields(FeedItemGame $newFeedItem, Game $game, GameImportRuleWikipedia $gameImportRule = null)
    {
        $modifiedFields = [];

        if ($gameImportRule == null) $gameImportRule = new GameImportRuleWikipedia;

        if (!$gameImportRule->shouldIgnoreDevelopers()) {
            if ($game->gameDevelopers()->count() == 0) {
                // Only proceed if new developer db entries do not exist
                if ($newFeedItem->item_developers != $game->developer) {
                    $modifiedFields[] = 'item_developers';
                }
            }
        }

        if (!$gameImportRule->shouldIgnorePublishers()) {
            if ($game->gamePublishers()->count() == 0) {
                // Only proceed if new publisher db entries do not exist
                if ($newFeedItem->item_publishers != $game->publisher) {
                    $modifiedFields[] = 'item_publishers';
                }
            }
        }

        if (!$gameImportRule->shouldIgnoreEuropeDates()) {
            if ($newFeedItem->release_date_eu != $game->eu_release_date) {
                $modifiedFields[] = 'release_date_eu';
            }
        }

        if (!$gameImportRule->shouldIgnoreUSDates()) {
            if ($newFeedItem->release_date_us != $game->us_release_date) {
                $modifiedFields[] = 'release_date_us';
            }
        }

        if (!$gameImportRule->shouldIgnoreJPDates()) {
            if ($newFeedItem->release_date_jp != $game->jp_release_date) {
                $modifiedFields[] = 'release_date_jp';
            }
        }

        return $modifiedFields;
    }
}