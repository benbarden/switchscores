<?php


namespace App\Services;

use App\Models\DataSource;
use App\Models\GameDeveloper;

use Illuminate\Support\Facades\DB;


class GameDeveloperService
{
    public function createGameDeveloper($gameId, $developerId)
    {
        GameDeveloper::create([
            'game_id' => $gameId,
            'developer_id' => $developerId
        ]);
    }

    public function delete($gameDeveloperId)
    {
        GameDeveloper::where('id', $gameDeveloperId)->delete();
    }

    public function deleteByGameId($gameId)
    {
        GameDeveloper::where('game_id', $gameId)->delete();
    }

    public function find($id)
    {
        return GameDeveloper::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        $gameDevelopers = GameDeveloper::where('game_id', '=', $gameId)->get();

        $gameDevelopers = $gameDevelopers->sortBy(function($gameDev) {
            return $gameDev->developer->name;
        });

        return $gameDevelopers;
    }

    public function getByDeveloperId($developerId)
    {
        return GameDeveloper::where('developer_id', $developerId)->get();
    }

    public function gameHasDeveloper($gameId, $developerId)
    {
        $gameTag = GameDeveloper::where('game_id', $gameId)
            ->where('developer_id', $developerId)
            ->first();
        if ($gameTag) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $developerId
     * @param bool $releasedOnly
     * @param null $limit
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection
     */
    public function getGamesByDeveloper($developerId, $releasedOnly = false, $limit = null)
    {
        $games = DB::table('games')
            ->join('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->join('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->select('games.*',
                'game_developers.developer_id',
                'games_companies.name',
                'games.eu_release_date')
            ->where('game_developers.developer_id', $developerId);

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