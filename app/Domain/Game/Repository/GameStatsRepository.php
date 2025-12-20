<?php

namespace App\Domain\Game\Repository;

use App\Domain\Cache\CacheManager;
use App\Domain\Repository\AbstractRepository;
use App\Enums\CacheDuration;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class GameStatsRepository extends AbstractRepository
{
    protected function getCachePrefix(): string
    {
        return "gamestats";
    }

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
    public function totalUpcoming()
    {
        return Game::where('eu_is_released', 0)->count();
    }

    /**
     * @return integer
     */
    public function totalRanked()
    {
        return Game::whereNotNull('game_rank')->where('format_digital', '<>', Game::FORMAT_DELISTED)->count();
    }


    /**
     * @return integer
     */
    public function getFormatDigital()
    {
        return DB::select("
            SELECT format_digital AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_digital
            ORDER BY format_digital
        ");
    }

    /**
     * @return integer
     */
    public function getFormatPhysical()
    {
        return DB::select("
            SELECT format_physical AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_physical
            ORDER BY format_physical
        ");
    }

    /**
     * @return integer
     */
    public function getFormatDLC()
    {
        return DB::select("
            SELECT format_dlc AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_dlc
            ORDER BY format_dlc
        ");
    }

    /**
     * @return integer
     */
    public function getFormatDemo()
    {
        return DB::select("
            SELECT format_demo AS format_desc, count(*) AS count
            FROM games
            GROUP BY format_demo
            ORDER BY format_demo
        ");
    }
}