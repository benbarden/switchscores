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

    /**
     * @param $region
     * @param $publisherId
     * @return mixed
     */
    public function getGamesByPublisher($region, $publisherId)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('publishers', 'game_publishers.publisher_id', '=', 'publishers.id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_publishers.publisher_id',
                'publishers.name')
            ->where('game_publishers.publisher_id', $publisherId)
            ->where('game_release_dates.region', $region)
            //->where('game_release_dates.is_released', '1')
            ->orderBy('game_release_dates.release_date', 'desc');

        $games = $games->get();
        return $games;
    }
}