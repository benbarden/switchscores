<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\GameDeveloper;


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

    public function find($id)
    {
        return GameDeveloper::find($id);
    }

    // ********************************************************** //

    public function getByGame($gameId)
    {
        return GameDeveloper::where('game_id', $gameId)->get();
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
     */
    public function getDevelopersNotOnGame($gameId)
    {
        $games = DB::select('
            select * from developers where id not in (select developer_id from game_developers where game_id = ?) ORDER BY name
        ', [$gameId]);

        return $games;
    }

    /**
     * @param $region
     * @param $developerId
     * @return mixed
     */
    public function getGamesByDeveloper($region, $developerId)
    {
        $games = DB::table('games')
            ->join('game_release_dates', 'games.id', '=', 'game_release_dates.game_id')
            ->join('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->join('developers', 'game_developers.developer_id', '=', 'developers.id')
            ->select('games.*',
                'game_release_dates.release_date',
                'game_release_dates.is_released',
                'game_release_dates.upcoming_date',
                'game_release_dates.release_year',
                'game_developers.developer_id',
                'developers.name')
            ->where('game_developers.developer_id', $developerId)
            ->where('game_release_dates.region', $region)
            //->where('game_release_dates.is_released', '1')
            ->orderBy('game_release_dates.release_date', 'desc');

        $games = $games->get();
        return $games;
    }

    // *** ACTION LISTS *** //

    public function getGamesWithNoDeveloper()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('developers', 'game_developers.developer_id', '=', 'developers.id')
            ->select('games.*', 'developers.name')
            ->whereNull('developers.id')
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
            left join developers d on d.id = gd.developer_id
            where d.id is null and g.developer is null;
        ');

        return $games[0]->count;
    }

    public function getNewDevelopersToSet()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('developers', 'game_developers.developer_id', '=', 'developers.id')
            ->select('games.*', 'developers.name')
            ->whereNull('developers.id')
            ->whereNotNull('games.developer')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countNewDevelopersToSet()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join developers d on d.id = gd.developer_id
            where d.id is null and g.developer is not null;
        ');

        return $games[0]->count;
    }

    public function getOldDevelopersToClear()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('developers', 'game_developers.developer_id', '=', 'developers.id')
            ->select('games.*', 'developers.name')
            ->whereNotNull('developers.id')
            ->whereNotNull('games.developer')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countOldDevelopersToClear()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join developers d on d.id = gd.developer_id
            where d.id is not null and g.developer is not null;
        ');

        return $games[0]->count;
    }

    public function getGameDeveloperLinks()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('developers', 'game_developers.developer_id', '=', 'developers.id')
            ->select('games.*', 'developers.name')
            ->whereNotNull('developers.id')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGameDeveloperLinks()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_developers gd on g.id = gd.game_id
            left join developers d on d.id = gd.developer_id
            where d.id is not null;
        ');

        return $games[0]->count;
    }
}