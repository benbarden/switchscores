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
        $feedItemGame->upcoming_date_eu = $this->crawlerModel->upcoming_date_eu;
        $feedItemGame->is_released_eu = $this->crawlerModel->is_released_eu;
        $feedItemGame->release_date_us = $this->crawlerModel->release_date_us;
        $feedItemGame->upcoming_date_us = $this->crawlerModel->upcoming_date_us;
        $feedItemGame->is_released_us = $this->crawlerModel->is_released_us;
        $feedItemGame->release_date_jp = $this->crawlerModel->release_date_jp;
        $feedItemGame->upcoming_date_jp = $this->crawlerModel->upcoming_date_jp;
        $feedItemGame->is_released_jp = $this->crawlerModel->is_released_jp;
        $feedItemGame->setStatusPending();

        return $feedItemGame;
    }

    public function getGameModifiedFields(FeedItemGame $newFeedItem, Game $game, Collection $gameReleaseDates, GameImportRuleWikipedia $gameImportRule = null)
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

        foreach ($gameReleaseDates as $gameReleaseDate) {

            $region = $gameReleaseDate->region;

            $skipDate = false;

            switch ($region) {
                case 'eu':
                    if ($gameImportRule->shouldIgnoreEuropeDates()) $skipDate = true;
                    break;
                case 'us':
                    if ($gameImportRule->shouldIgnoreUSDates()) $skipDate = true;
                    break;
                case 'jp':
                    if ($gameImportRule->shouldIgnoreJPDates()) $skipDate = true;
                    break;
            }

            if (!$skipDate) {
                $releaseDateField = 'release_date_'.$region;
                $upcomingDateField = 'upcoming_date_'.$region;

                if ($newFeedItem->{$releaseDateField} != $gameReleaseDate->release_date) {
                    $modifiedFields[] = $releaseDateField;
                }
                if ($newFeedItem->{$upcomingDateField} != $gameReleaseDate->upcoming_date) {
                    $modifiedFields[] = $upcomingDateField;
                }
            }

        }

        return $modifiedFields;
    }

    /**
     * @deprecated
     * @param FeedItemGame $newFeedItem
     * @param FeedItemGame $lastFeedItem
     * @return array
     */
    public function getFeedItemModifiedFields(FeedItemGame $newFeedItem, FeedItemGame $lastFeedItem)
    {
        $modifiedFields = [];

        $fieldList = [
            'item_genre', 'item_developers', 'item_publishers',
            'release_date_eu', 'upcoming_date_eu',
            'release_date_us', 'upcoming_date_us',
            'release_date_jp', 'upcoming_date_jp',
        ];

        foreach ($fieldList as $field) {
            if ($newFeedItem->{$field} != $lastFeedItem->{$field}) {
                $modifiedFields[] = $field;
            }
        }

        return $modifiedFields;
    }
}