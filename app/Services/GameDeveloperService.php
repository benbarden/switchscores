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

}