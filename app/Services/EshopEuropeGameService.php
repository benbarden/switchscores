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

    public function getAllWithLink($limit = null, $count = false)
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
        if ($count) {
            $feedItems = $feedItems->count();
        } else {
            $feedItems = $feedItems->get();
        }

        return $feedItems;
    }

    public function getAllWithoutLink($limit = null, $count = false)
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
        if ($count) {
            $feedItems = $feedItems->count();
        } else {
            $feedItems = $feedItems->get();
        }

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
                'games.price_eshop',
                'games.game_rank',
                'games.rating_avg')
            ->where('eshop_europe_games.price_has_discount_b', '=', 1)
            ->orderBy('eshop_europe_games.price_discount_percentage_f', 'desc');
        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }
        $feedItems = $feedItems->get();

        return $feedItems;
    }

    /**
     * Gets the highest available discounts.
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getGamesOnSaleHighestDiscounts($limit = 50)
    {
        $games = DB::table('eshop_europe_games')
            ->join('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->leftJoin('game_primary_types', 'games.primary_type_id', '=', 'game_primary_types.id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id',
                'games.title AS game_title',
                'games.link_title',
                'games.price_eshop',
                'eshop_europe_games.price_lowest_f',
                'eshop_europe_games.price_discount_percentage_f',
                'games.game_rank',
                'games.rating_avg',
                'games.boxart_header_image',
                'games.primary_type_id',
                'game_primary_types.primary_type',
                'games.review_count')
            ->whereNotNull('games.game_rank')
            ->where('eshop_europe_games.price_has_discount_b', '=', 1)
            ->where('eshop_europe_games.price_discount_percentage_f', '>=', 50)
            ->orderBy('games.game_rank', 'asc')
            ->orderBy('eshop_europe_games.price_discount_percentage_f', 'desc');
        if ($limit) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * Gets good discounts for green rated games
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getGamesOnSaleGoodRanks($limit = 50)
    {
        $games = DB::table('eshop_europe_games')
            ->join('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->leftJoin('game_primary_types', 'games.primary_type_id', '=', 'game_primary_types.id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id',
                'games.title AS game_title',
                'games.link_title',
                'games.price_eshop',
                'eshop_europe_games.price_lowest_f',
                'eshop_europe_games.price_discount_percentage_f',
                'games.game_rank',
                'games.rating_avg',
                'games.boxart_header_image',
                'games.primary_type_id',
                'game_primary_types.primary_type',
                'games.review_count')
            ->whereNotNull('games.game_rank')
            ->where('games.rating_avg', '>', '7.9')
            ->where('eshop_europe_games.price_has_discount_b', '=', 1)
            ->where('eshop_europe_games.price_discount_percentage_f', '>=', 25)
            ->orderBy('games.rating_avg', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    /**
     * Gets unranked games that are on sale
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getGamesOnSaleUnranked($limit = 50)
    {
        $games = DB::table('eshop_europe_games')
            ->join('games', 'eshop_europe_games.fs_id', '=', 'games.eshop_europe_fs_id')
            ->leftJoin('game_primary_types', 'games.primary_type_id', '=', 'game_primary_types.id')
            ->select('eshop_europe_games.*',
                'games.id AS game_id',
                'games.title AS game_title',
                'games.link_title',
                'games.price_eshop',
                'eshop_europe_games.price_lowest_f',
                'eshop_europe_games.price_discount_percentage_f',
                'games.game_rank',
                'games.rating_avg',
                'games.boxart_header_image',
                'games.primary_type_id',
                'game_primary_types.primary_type',
                'games.review_count')
            ->whereNull('games.game_rank')
            ->where('eshop_europe_games.price_has_discount_b', '=', 1)
            ->orderBy('eshop_europe_games.price_discount_percentage_f', 'desc');

        if ($limit) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }
}