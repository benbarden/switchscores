<?php

namespace App\Domain\GameStats;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function siteStatsByConsole($consoleId)
    {
        return DB::select("
            SELECT SUM(if(eu_is_released and format_digital <> ?, 1, 0)) as total_count, 
            SUM(if(game_rank is not null and format_digital <> ?, 1, 0)) as ranked_count, 
            SUM(if(eu_is_released = 1 and format_digital <> ?, 1, 0)) as released_count, 
            SUM(if(is_low_quality = 1 and format_digital <> ?, 1, 0)) as low_quality_count
            from games
            where console_id = ?
        ", [Game::FORMAT_DELISTED, Game::FORMAT_DELISTED, Game::FORMAT_DELISTED, Game::FORMAT_DELISTED, $consoleId]);
    }
}
