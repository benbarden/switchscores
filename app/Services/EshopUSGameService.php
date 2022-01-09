<?php


namespace App\Services;

use App\Models\EshopUSGame;
use Illuminate\Support\Facades\DB;


class EshopUSGameService
{
    /**
     * @param $id
     * @return EshopUSGame
     */
    public function find($id)
    {
        return EshopUSGame::find($id);
    }

    /**
     * @param $title
     * @return EshopUSGame
     */
    public function getByTitle($title)
    {
        $eshopGame = EshopUSGame::where('title', $title)->first();
        return $eshopGame;
    }

    /**
     * @param $nsuId
     * @return EshopUSGame
     */
    public function getByNsuId($nsuId)
    {
        $eshopGame = EshopUSGame::where('nsuid', $nsuId)->first();
        return $eshopGame;
    }

    public function getAll($limit = null)
    {
        $feedItems = EshopUSGame
            ::orderBy('title', 'asc');

        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }

        $feedItems = $feedItems->get();

        return $feedItems;
    }

    public function getTotalCount()
    {
        return EshopUSGame::orderBy('title', 'asc')->count();
    }

    public function getAllWithLink($limit = null, $count = false)
    {
        $feedItems = DB::table('eshop_us_games')
            ->leftJoin('games', 'eshop_us_games.nsuid', '=', 'games.eshop_us_nsuid')
            ->select('eshop_us_games.*',
                'games.id AS game_id')
            ->whereNotNull('games.id')
            ->orderBy('eshop_us_games.title', 'asc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        if ($count) {
            $feedItems = $feedItems->count();
        } else {
            $feedItems = $feedItems->get();
        }

        return $feedItems;
    }

    public function getAllWithoutLink($limit = null, $count = false)
    {
        $feedItems = DB::table('eshop_us_games')
            ->leftJoin('games', 'eshop_us_games.nsuid', '=', 'games.eshop_us_nsuid')
            ->select('eshop_us_games.*',
                'games.id AS game_id')
            ->whereNull('games.id')
            ->orderBy('eshop_us_games.title', 'asc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        if ($count) {
            $feedItems = $feedItems->count();
        } else {
            $feedItems = $feedItems->get();
        }

        return $feedItems;
    }

    public function getGamesOnSale($limit = null)
    {
        $feedItems = DB::table('eshop_us_games')
            ->leftJoin('games', 'eshop_us_games.nsuid', '=', 'games.eshop_us_nsuid')
            ->select('eshop_us_games.*',
                'games.id AS game_id',
                'games.title AS game_title',
                'games.link_title AS game_link_title',
                'games.price_eshop',
                'eshop_us_games.sale_price',
                'games.game_rank',
                'games.rating_avg')
            ->whereNotNull('eshop_us_games.sale_price')
            ->orderBy('eshop_us_games.sale_price', 'desc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        $feedItems = $feedItems->get();

        return $feedItems;
    }
}