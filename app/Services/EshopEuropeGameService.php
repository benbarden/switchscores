<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\EshopEuropeGame;


class EshopEuropeGameService
{
    /**
     * @param $id
     * @return EshopEuropeGame
     */
    public function find($id)
    {
        return EshopEuropeGame::find($id);
    }

    /**
     * @param $title
     * @return EshopEuropeGame
     */
    public function getByTitle($title)
    {
        $eshopGame = EshopEuropeGame::where('title', $title)->first();
        return $eshopGame;
    }

    /**
     * @param $fsId
     * @return EshopEuropeGame
     */
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

    public function getTotalCount()
    {
        return EshopEuropeGame::orderBy('title', 'asc')->count();
    }

    public function getAllWithLink($ignoreFsIdList = null, $limit = null, $count = false)
    {
        $feedItems = DB::table('eshop_europe_games')
            ->leftJoin('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id')
            ->whereNotNull('games.id');
        if ($ignoreFsIdList) {
            $feedItems = $feedItems->whereNotIn('eshop_europe_games.fs_id', $ignoreFsIdList);
        }
        $feedItems = $feedItems->orderBy('eshop_europe_games.title', 'asc');
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

    public function getAllWithoutLink($ignoreFsIdList = null, $limit = null, $count = false)
    {
        $feedItems = DB::table('eshop_europe_games')
            ->leftJoin('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id')
            ->whereNull('games.id');
        if ($ignoreFsIdList) {
            $feedItems = $feedItems->whereNotIn('eshop_europe_games.fs_id', $ignoreFsIdList);
        }
        $feedItems = $feedItems->orderBy('eshop_europe_games.title', 'asc');
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

    public function getByFsIdList($fsIdList)
    {
        $feedItems = DB::table('eshop_europe_games')
            ->leftJoin('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id')
            ->whereNull('games.id')
            ->whereIn('eshop_europe_games.fs_id', $fsIdList)
            ->orderBy('eshop_europe_games.title', 'asc')
            ->get();
        return $feedItems;
    }
}