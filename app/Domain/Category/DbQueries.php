<?php

namespace App\Domain\Category;

use Illuminate\Support\Facades\DB;

class DbQueries
{
    /**
     * @param $categoryId
     * @param int $limit
     * @return mixed
     */
    public function getRankedByCategory($categoryId, $limit = null)
    {
        $games = DB::table('games')
            ->select('games.*')
            ->where('games.eu_is_released', 1)
            ->where('games.category_id', $categoryId)
            ->whereNotNull('games.game_rank')
            ->orderBy('games.rating_avg', 'desc')
            ->orderBy('games.title', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }
}