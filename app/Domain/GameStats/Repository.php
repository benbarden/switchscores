<?php


namespace App\Domain\GameStats;


use App\Models\Game;
use Illuminate\Support\Facades\DB;

class Repository
{
    /**
     * @return integer
     */
    public function grandTotal()
    {
        return Game::orderBy('title', 'asc')->count();
    }

    /**
     * @return integer
     */
    public function totalReleased()
    {
        return Game::where('eu_is_released', 1)->where('format_digital', '<>', Game::FORMAT_DELISTED)->count();
    }

    /**
     * @return integer
     */
    public function totalRanked()
    {
        return Game::whereNotNull('game_rank')->count();
    }

    /**
     * @return integer
     */
    public function totalNoCategory()
    {
        return Game::whereNull('category_id')->count();
    }

    /**
     * @return integer
     */
    public function totalUntagged()
    {
        return Game::whereDoesntHave('gameTags')->count();
    }

    /**
     * @return integer
     */
    public function totalToBeReleased()
    {
        $games = Game::where('eu_is_released', 0)
            ->whereRaw('DATE(games.eu_release_date) <= CURDATE()')
            ->count();

        return $games;
    }

    public function tableFormatOptions()
    {
        return DB::select("
            SELECT 
                   CASE WHEN format_digital IS NULL THEN 'HdrDigital' ELSE format_digital END AS format_desc, 
                   'Digital' AS format_type,
                   count(*) AS count FROM games GROUP BY format_digital
            UNION
            SELECT 
                   CASE WHEN format_physical IS NULL THEN 'HdrPhysical' ELSE format_physical END AS format_desc, 
                   'Physical' AS format_type,
                   count(*) AS count FROM games GROUP BY format_physical
            UNION
            SELECT 
                   CASE WHEN format_dlc IS NULL THEN 'HdrDLC' ELSE format_dlc END AS format_desc, 
                   'DLC' AS format_type,
                   count(*) AS count FROM games GROUP BY format_dlc
            UNION
            SELECT 
                   CASE WHEN format_demo IS NULL THEN 'HdrDemo' ELSE format_demo END AS format_desc, 
                   'Demo' AS format_type,
                   count(*) AS count FROM games GROUP BY format_demo
        ");
    }

    public function totalNoVideoType()
    {
        return Game::whereNull('video_type')->count();
    }
}