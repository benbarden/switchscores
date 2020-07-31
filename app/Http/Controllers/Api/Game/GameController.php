<?php

namespace App\Http\Controllers\Api\Game;

use App\Traits\SwitchServices;

class GameController
{
    use SwitchServices;

    public function getList()
    {
        $gameList = $this->getServiceGame()->getApiIdList();

        if ($gameList) {
            return response()->json(['games' => $gameList], 200);
        } else {
            return response()->json(['message' => 'Error retrieving game list'], 400);
        }
    }

    public function getDetails($gameId)
    {
        $game = $this->getServiceGame()->find($gameId);
        if (!$game) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $gameData = [
            'id' => $game->id,
            'title' => $game->title,
            'link_title' => $game->link_title,
            'price_eshop' => $game->price_eshop,
            'players' => $game->players,
            'rating_avg' => $game->rating_avg,
            'review_count' => $game->review_count,
            'game_rank' => $game->game_rank,
            'video_url' => $game->video_url,
            'eu_release_date' => $game->eu_release_date,
            'us_release_date' => $game->us_release_date,
            'jp_release_date' => $game->jp_release_date,
            'updated_at' => $game->updated_at,
            'category' => $game->category,
            'series' => $game->series,
            'developers' => $game->gameDevelopers,
            'publishers' => $game->gamePublishers,
            'eshop_europe_fs_id' => $game->eshop_europe_fs_id,
            'dspNintendoCoUk' => $game->dspNintendoCoUk,
        ];

        return response()->json(['game' => $gameData], 200);
    }

    public function getReviews($gameId)
    {
        $game = $this->getServiceGame()->find($gameId);
        if (!$game) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $gameReviews = $this->getServiceReviewLink()->getByGame($gameId);

        return response()->json(['reviews' => $gameReviews], 200);
    }

    public function getListXX()
    {
        $serviceGame = $this->getServiceGame();

        $request = request();

        $title = $request->title;
        $matchRule = $request->matchRule;
        $matchIndex = $request->matchIndex;

        if (!$title) {
            return response()->json(['error' => 'Missing data: title'], 400);
        }
        if (!$matchRule) {
            $matchRule = '';
            $matchIndex = 0;
        }

        $serviceTitleMatch = new ServiceTitleMatch();
        $serviceTitleMatch->setMatchRule($matchRule);
        $serviceTitleMatch->setMatchIndex($matchIndex);
        $matchedTitle = $serviceTitleMatch->generate($title);

        $game = $serviceGame->getByTitle($matchedTitle);
        if ($game) {
            return response()->json(['id' => $game->id], 200);
        } else {
            return response()->json(['message' => 'Not found'], 404);
        }
    }
}
