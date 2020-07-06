<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GameDeveloper;
use App\Partner;


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
     * Helper method to get all of the developers that haven't been applied to the current game yet.
     * @param $gameId
     * @return array
     */
    public function getDevelopersNotOnGame($gameId)
    {
        $games = DB::select('
            select * from partners
            where type_id = ?
            and id not in (select developer_id from game_developers where game_id = ?)
            ORDER BY name
        ', [Partner::TYPE_GAMES_COMPANY, $gameId]);

        return $games;
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
            ->join('partners', 'game_developers.developer_id', '=', 'partners.id')
            ->select('games.*',
                'game_developers.developer_id',
                'partners.name',
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

    // *** ACTION LISTS *** //

    public function getGamesWithNoDeveloper()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('partners', 'game_developers.developer_id', '=', 'partners.id')
            ->select('games.*', 'partners.name')
            ->whereNull('partners.id')
            ->whereNull('games.developer')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithNoDeveloper()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join partners d on d.id = gd.developer_id
            where d.id is null and g.developer is null;
        ');

        return $games[0]->count;
    }

    public function getGamesWithOldDevFieldSet()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('partners', 'game_developers.developer_id', '=', 'partners.id')
            ->select('games.*', 'partners.name')
            //->whereNull('partners.id')
            ->whereNotNull('games.developer')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithOldDevFieldSet()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join partners d on d.id = gd.developer_id
            where g.developer is not null;
        ');

        return $games[0]->count;
    }

    public function countGameDeveloperLinks()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join partners d on d.id = gd.developer_id
            where d.id is not null;
        ');

        return $games[0]->count;
    }
}