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
}