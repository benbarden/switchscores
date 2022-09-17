<?php

namespace App\Domain\GamePublisher;

use App\Models\DataSource;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getGamesWithNoPublisher()
    {
        $games = DB::table('games')
            ->leftJoin('game_publishers', 'games.id', '=', 'game_publishers.game_id')
            ->leftJoin('games_companies', 'game_publishers.publisher_id', '=', 'games_companies.id')
            ->leftJoin('data_source_parsed AS dsp_nintendo_co_uk', function ($join) {
                $join->on('games.id', '=', 'dsp_nintendo_co_uk.game_id')
                    ->where('dsp_nintendo_co_uk.source_id', '=', DataSource::DSID_NINTENDO_CO_UK);
            })
            ->select('games.*',
                'dsp_nintendo_co_uk.publishers AS nintendo_co_uk_publishers',
                'games_companies.name')
            ->whereNull('games_companies.id')
            ->where('games.format_digital', '<>', Game::FORMAT_DELISTED)
            ->orWhereNull('games.format_digital')
            ->orderBy('games.id', 'desc');

        $games = $games->get();
        return $games;
    }

    public function countGamesWithNoPublisher()
    {
        $games = DB::select('
            select count(*) AS count
            from games g
            left join game_publishers gp on g.id = gp.game_id
            left join games_companies gc on gc.id = gp.publisher_id
            where gc.id is null
            and (format_digital <> ? OR format_digital IS NULL)
        ', [Game::FORMAT_DELISTED]);

        return $games[0]->count;
    }

}