<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\EshopEuropeGame;


class EshopEuropeGameService
{
    public function find($id)
    {
        return EshopEuropeGame::find($id);
    }

    public function getByTitle($title)
    {
        $eshopGame = EshopEuropeGame::where('title', $title)->first();
        return $eshopGame;
    }

    public function getByFsId($fsId)
    {
        $eshopGame = EshopEuropeGame::where('fs_id', $fsId)->first();
        return $eshopGame;
    }

    public function getAll($limit = null)
    {
        $feedItems = EshopEuropeGame
            ::orderBy('title', 'asc');

        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }

        $feedItems = $feedItems->get();

        return $feedItems;
    }

    public function getAllWithLink($limit = null)
    {
        $feedItems = DB::table('eshop_europe_games')
            ->leftJoin('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id')
            ->whereNotNull('games.id')
            ->orderBy('eshop_europe_games.title', 'asc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        $feedItems = $feedItems->get();

        return $feedItems;
    }

    public function getAllWithoutLink($limit = null)
    {
        $feedItems = DB::table('eshop_europe_games')
            ->leftJoin('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id')
            ->whereNull('games.id')
            ->orderBy('eshop_europe_games.title', 'asc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        $feedItems = $feedItems->get();

        return $feedItems;
    }

    public function getGamesOnSale($limit = null)
    {
        $feedItems = DB::table('eshop_europe_games')
            ->leftJoin('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id',
                'games.title AS game_title',
                'games.link_title AS game_link_title',
                'games.price_eshop')
            ->where('eshop_europe_games.price_has_discount_b', '=', 1)
            ->orderBy('eshop_europe_games.price_discount_percentage_f', 'desc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        $feedItems = $feedItems->get();

        return $feedItems;
    }
}