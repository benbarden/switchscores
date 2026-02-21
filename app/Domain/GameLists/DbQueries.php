<?php


namespace App\Domain\GameLists;


use App\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getByTagWithDates($tagId)
    {
        $games = DB::table('games')
            ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
            ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
            ->select('games.*',
                'game_tags.tag_id',
                'tags.tag_name')
            ->where('game_tags.tag_id', $tagId)
            //->where('games.eu_is_released', '1')
            ->where('games.game_status', GameStatus::ACTIVE->value)
            ->orderBy('games.title', 'asc');
            //->orderBy('games.rating_avg', 'desc')
            //->orderBy('games.eu_release_date', 'desc');

        $games = $games->get();
        return $games;
    }

    // ********* NINTENDO.CO.UK API - Games on sale ************** //

    /**
     * Gets the highest available discounts.
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function onSaleHighestDiscounts($limit = 50)
    {
        $games = DB::table('games')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'categories.name AS category_name')
            ->whereNotNull('games.game_rank')
            ->where('games.format_digital', Game::FORMAT_AVAILABLE)
            ->whereNotNull('games.price_eshop_discounted')
            ->where('games.price_eshop_discount_pc', '>=', '50')
            ->orderBy('games.game_rank', 'asc')
            ->orderBy('games.price_eshop_discount_pc', 'desc');
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
    public function onSaleGoodRanks($limit = 50)
    {
        $games = DB::table('games')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'categories.name AS category_name')
            ->whereNotNull('games.game_rank')
            ->where('games.format_digital', Game::FORMAT_AVAILABLE)
            ->where('games.rating_avg', '>', '7.9')
            ->whereNotNull('games.price_eshop_discounted')
            ->where('games.price_eshop_discount_pc', '>=', '25.0')
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
    public function onSaleUnranked($limit = 50)
    {
        $games = DB::table('games')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'categories.name AS category_name')
            ->whereNull('games.game_rank')
            ->where('games.format_digital', Game::FORMAT_AVAILABLE)
            ->whereNotNull('games.price_eshop_discounted')
            ->orderBy('games.price_eshop_discount_pc', 'desc');
        if ($limit) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }
}