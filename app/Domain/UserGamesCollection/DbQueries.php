<?php


namespace App\Domain\UserGamesCollection;


use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getCategoryBreakdown($userId)
    {
        return DB::select('
            SELECT g.*, c.name, count(*) AS count
            FROM user_games_collection ugc
            JOIN games g ON g.id = ugc.game_id
            JOIN categories c ON g.category_id = c.id
            WHERE ugc.user_id = ?
            GROUP BY g.category_id
            ORDER BY count(*) DESC
        ', [$userId]);
    }
}