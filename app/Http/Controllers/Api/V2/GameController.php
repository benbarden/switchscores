<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Game;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

class GameController
{
    private $repoGame;
    private $repoGameLists;

    public function __construct(
        GameRepository $repoGame,
        GameListsRepository $repoGameLists
    )
    {
        $this->repoGame = $repoGame;
        $this->repoGameLists = $repoGameLists;
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
            'reviews' => null,
            'game_rank' => $game->game_rank,
            'category' => $game->category,
            'series' => $game->series,
            'format_digital' => $game->format_digital,
            'format_physical' => $game->format_physical,
            'format_dlc' => $game->format_dlc,
            'format_demo' => $game->format_demo,
            'video_url' => $game->video_url,
            'amazon_uk_url' => null,
            'amazon_uk_url_tagged' => null,
            'developers' => null,
            'publishers' => null,
            'eu_release_date' => $game->eu_release_date,
            'us_release_date' => $game->us_release_date,
            'jp_release_date' => $game->jp_release_date,
            'eshop_europe_fs_id' => $game->eshop_europe_fs_id,
            'dspNintendoCoUk' => $game->dspNintendoCoUk,
            'updated_at' => $game->updated_at,
        ];

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

        if ($game->amazon_uk_link) {
            $gameData['amazon_uk_url'] = $game->amazon_uk_link;
            $gameData['amazon_uk_url_tagged'] = $game->amazon_uk_link.'?tag=switchscores-21';
        } else {
            unset($gameData['amazon_uk_url']);
            unset($gameData['amazon_uk_url_tagged']);
        }

        return $gameData;
    }
}
