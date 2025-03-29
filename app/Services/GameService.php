<?php


namespace App\Services;

use App\Models\Category;
use App\Models\Game;
use App\Models\GameSeries;
use App\Models\GameTag;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;


class GameService
{

    public function getWithNoNintendoCoUkLink($limit = null)
    {
        $gameList = Game::whereNull('eshop_europe_fs_id')
            ->whereNull('nintendo_store_url_override')
            ->whereNotNull('eu_release_date')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('id', 'desc');
        if ($limit) {
            $gameList = $gameList->limit($limit);
        }
        return $gameList->get();
    }

    public function getWithBrokenNintendoCoUkLink($limit = null)
    {
        $gameList = DB::table('games')
            ->select('games.*')
            ->leftJoin('data_source_parsed', 'games.eshop_europe_fs_id', '=', 'data_source_parsed.link_id')
            ->whereNotNull('games.eshop_europe_fs_id')
            ->whereNull('data_source_parsed.link_id')
            ->whereNull('games.nintendo_store_url_override');
        if ($limit) {
            $gameList = $gameList->limit($limit);
        }
        return $gameList->get();
    }

    /**
     * @deprecated
     * @return \Illuminate\Support\Collection
     */
    public function getAll()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

    public function getGamesForSitemap()
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('games.title', 'asc');
        $games = $games->get();

        return $games;
    }

}