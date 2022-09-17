<?php

namespace App\Domain\GameDeveloper;

use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getGamesWithNoDeveloper()
    {
        $games = DB::table('games')
            ->leftJoin('game_developers', 'games.id', '=', 'game_developers.game_id')
            ->leftJoin('games_companies', 'game_developers.developer_id', '=', 'games_companies.id')
            ->select('games.*',
                'games_companies.name')
            ->whereNull('games_companies.id')
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
            left join games_companies gc on gc.id = gd.developer_id
            where gc.id is null
        ');

        return $games[0]->count;
    }

}