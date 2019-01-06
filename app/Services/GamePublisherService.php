<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GamePublisher;


class GamePublisherService
{
    public function createGamePublisher($gameId, $publisherId)
    {
        GamePublisher::create([
            'game_id' => $gameId,
            'publisher_id' => $publisherId
        ]);
    }

    public function delete($gamePublisherId)
    {
        GamePublisher::where('id', $gamePublisherId)->delete();
    }

    public function find($id)
    {
        return GamePublisher::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        return GamePublisher::where('game_id', $gameId)->get();
    }

    public function getByPublisherId($publisherId)
    {
        return GamePublisher::where('publisher_id', $publisherId)->get();
    }

    public function gameHasPublisher($gameId, $publisherId)
    {
        $gameTag = GamePublisher::where('game_id', $gameId)
            ->where('publisher_id', $publisherId)
            ->first();
        if ($gameTag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Helper method to get all of the publishers that haven't been applied to the current game yet.
     * @param $gameId
     */
    public function getPublishersNotOnGame($gameId)
    {
        $games = DB::select('
            select * from publishers where id not in (select publisher_id from game_publishers where game_id = ?) ORDER BY name
        ', [$gameId]);

        return $games;
    }
}