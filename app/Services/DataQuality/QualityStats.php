<?php

namespace App\Services\DataQuality;

use App\Models\Game;
use Illuminate\Support\Facades\DB;

class QualityStats
{
    // Data integrity checks
    public function getDuplicateReviews()
    {
        return DB::select('
            SELECT rl.game_id, g.title AS game_title, rl.site_id, p.name AS partner_name, count(*) AS count
            FROM review_links rl
            JOIN games g ON rl.game_id = g.id
            JOIN games_companies p ON rl.site_id = p.id
            GROUP BY rl.game_id, rl.site_id
            HAVING count(*) > 1
        ');
    }

    // *** Categories - all months *** //
    public function getCategoryStats()
    {
        return DB::select('
            SELECT
            DATE_FORMAT(eu_release_date, \'%Y-%m\') AS yearmonth,
            DATE_FORMAT(eu_release_date, \'%Y\') AS release_year,
            DATE_FORMAT(eu_release_date, \'%m\') AS release_month,
            count(category_id) AS has_category, 
            count(*) - count(category_id) AS no_category, 
            count(*) AS total_count,
            round((count(category_id) / count(*)) * 100, 2) AS pc_done
            FROM games
            GROUP BY yearmonth
            ORDER BY yearmonth DESC
        ');
    }

    public function getGamesWithCategory($year, $month)
    {
        return Game::whereNotNull('category_id')
            ->whereYear('eu_release_date', $year)
            ->whereMonth('eu_release_date', $month)
            ->orderBy('eu_release_date', 'asc')
            ->get();
    }

    public function getGamesWithoutCategory($year, $month)
    {
        return Game::whereNull('category_id')
            ->whereYear('eu_release_date', $year)
            ->whereMonth('eu_release_date', $month)
            ->orderBy('eu_release_date', 'asc')
            ->get();
    }

}