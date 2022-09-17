<?php


namespace App\Services;

use App\Models\DataSource;
use App\Models\Game;
use App\Models\GamePublisher;

use Illuminate\Support\Facades\DB;


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

    public function deleteByGameId($gameId)
    {
        GamePublisher::where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return GamePublisher::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        $gamePublishers = GamePublisher::where('game_id', '=', $gameId)->get();

        $gamePublishers = $gamePublishers->sortBy(function($gamePub) {
            return $gamePub->publisher->name;
        });

        return $gamePublishers;
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
     * @param $publisherId
     * @param bool $releasedOnly
     * @param null $limit
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function getGamesByPublisher($publisherId, $releasedOnly = false, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->join('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_publishers.publisher_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_publishers.publisher_id', $publisherId);

        if ($releasedOnly) {
            $games = $games->where('games.eu_is_released', '1');
        }

        $games = $games->orderBy('games.eu_release_date', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        $games = $games->get();
        return $games;
    }
}