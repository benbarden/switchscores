<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Game;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\AffiliateCodes\Amazon as AmazonAffiliate;

class GameController
{
    public function __construct(
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists,
        private AmazonAffiliate $affiliateAmazon
    )
    {
    }

    public function getGameDetails($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $gameData = $this->parseGameData($game);

        return response()->json(['game' => $gameData], 200);
    }

    public function getDetailsByLinkId($linkId)
    {
        if (str_contains($linkId, ",")) {
            $linkIdList = explode(",", $linkId);
        } else {
            $linkIdList = [$linkId];
        }

        $gameDataList = [];

        foreach ($linkIdList as $linkIdItem) {

            $game = $this->repoGame->getByEshopEuropeId($linkIdItem);
            if ($game) {
                $gameDataList[] = ['game' => $this->parseGameData($game)];
            }

        }

        if (count($gameDataList) == 0) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json($gameDataList, 200);
    }

    public function getList()
    {
        $gameList = $this->repoGameLists->getApiIdList();
        if ($gameList) {
            return response()->json(['games' => $gameList], 200);
        } else {
            return response()->json(['message' => 'Error retrieving game list'], 400);
        }
    }

    private function parseGameData(Game $game)
    {
        $gameUrl = route('game.show', ['id' => $game->id, 'linkTitle' => $game->link_title]);
        $gameData = [
            'id' => $game->id,
            'title' => $game->title,
            'url' => $gameUrl,
            'price_eshop' => $game->price_eshop,
            'players' => $game->players,
            'rating_avg' => $game->rating_avg,
            'review_count' => $game->review_count,
            'game_rank' => $game->game_rank,
            'category' => null,
            'series' => null,
            'format_digital' => $game->format_digital,
            'format_physical' => $game->format_physical,
            'format_dlc' => $game->format_dlc,
            'format_demo' => $game->format_demo,
            'video_url' => $game->video_url,
            'amazon_uk_url' => null,
            'amazon_us_url' => null,
            'developers' => null,
            'publishers' => null,
            'tags' => null,
            'eu_release_date' => $game->eu_release_date,
            'us_release_date' => $game->us_release_date,
            'jp_release_date' => $game->jp_release_date,
            'eshop_europe_fs_id' => $game->eshop_europe_fs_id,
            'dspNintendoCoUk' => $game->dspNintendoCoUk,
            'updated_at' => $game->updated_at,
            'reviews' => null,
        ];

        if ($game->category) {
            $gameData['category'] = [
                'id' => $game->category->id,
                'name' => $game->category->name,
            ];
            if ($game->category->parent) {
                $gameData['category']['parent'] = [
                    'id' => $game->category->parent->id,
                    'name' => $game->category->parent->name
                ];
            }
        }

        if ($game->series) {
            $gameData['series'] = [
                'id' => $game->series->id,
                'name' => $game->series->series,
            ];
        }

        if ($game->reviews->count() > 0) {
            $listReviews = [];
            foreach ($game->reviews as $review) {
                $listReviews[] = [
                    'review_date' => $review->review_date,
                    'rating_normalised' => $review->rating_normalised,
                    'url' => $review->url,
                    'site_name' => $review->site->name,
                ];
            }
            $gameData['reviews'] = $listReviews;
        }

        if ($game->gameDevelopers->count() > 0) {
            $listDevelopers = [];
            foreach ($game->gameDevelopers as $gamesCompany) {
                $listDevelopers[] = [
                    'name' => $gamesCompany->developer->name
                ];
            }
            $gameData['developers'] = $listDevelopers;
        }

        if ($game->gamePublishers->count() > 0) {
            $listPublishers = [];
            foreach ($game->gamePublishers as $gamesCompany) {
                $listPublishers[] = [
                    'name' => $gamesCompany->publisher->name
                ];
            }
            $gameData['publishers'] = $listPublishers;
        }

        if ($game->gameTags->count() > 0) {
            $listTags = [];
            foreach ($game->gameTags as $gameTag) {
                $listTags[] = [
                    'name' => $gameTag->tag->tag_name
                ];
            }
            $gameData['tags'] = $listTags;
        }

        if ($game->amazon_uk_link) {
            $amazonId = $this->affiliateAmazon->getUKId();
            $gameData['amazon_uk_url'] = $game->amazon_uk_link.'?tag='.$amazonId;
        } else {
            unset($gameData['amazon_uk_url']);
        }

        if ($game->amazon_us_link) {
            $amazonId = $this->affiliateAmazon->getUSId();
            $gameData['amazon_us_url'] = $game->amazon_us_link.'?tag='.$amazonId;
        } else {
            unset($gameData['amazon_us_url']);
        }

        return $gameData;
    }
}
