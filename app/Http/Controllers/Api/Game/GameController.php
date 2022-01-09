<?php

namespace App\Http\Controllers\Api\Game;

use App\Models\Game;
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
            'category' => $game->category,
            'series' => $game->series,
            'format_digital' => $game->format_digital,
            'format_physical' => $game->format_physical,
            'format_dlc' => $game->format_dlc,
            'format_demo' => $game->format_demo,
            'video_url' => $game->video_url,
            'amazon_uk_url' => null,
            'amazon_uk_url_tagged' => null,
            'developers' => $game->gameDevelopers,
            'publishers' => $game->gamePublishers,
            'eu_release_date' => $game->eu_release_date,
            'us_release_date' => $game->us_release_date,
            'jp_release_date' => $game->jp_release_date,
            'eshop_europe_fs_id' => $game->eshop_europe_fs_id,
            'dspNintendoCoUk' => $game->dspNintendoCoUk,
            'updated_at' => $game->updated_at,
        ];
        if ($game->amazon_uk_link) {
            $gameData['amazon_uk_url'] = $game->amazon_uk_link;
            $gameData['amazon_uk_url_tagged'] = $game->amazon_uk_link.'?tag=switchscores-21';
        } else {
            unset($gameData['amazon_uk_url']);
            unset($gameData['amazon_uk_url_tagged']);
        }

        return $gameData;
    }

    public function getDetails($gameId)
    {
        $game = $this->getServiceGame()->find($gameId);
        if (!$game) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $gameData = $this->parseGameData($game);

        return response()->json(['game' => $gameData], 200);
    }

    public function getDetailsByLinkId($linkId)
    {
        if (strpos($linkId, ",") !== false) {
            $linkIdList = explode(",", $linkId);
        } else {
            $linkIdList = [$linkId];
        }

        $gameDataList = [];

        foreach ($linkIdList as $linkIdItem) {

            $game = $this->getServiceGame()->getByEshopEuropeId($linkIdItem);
            if ($game) {
                $gameDataList[] = ['game' => $this->parseGameData($game)];
            }

        }

        if (count($gameDataList) == 0) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json($gameDataList, 200);
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
}
