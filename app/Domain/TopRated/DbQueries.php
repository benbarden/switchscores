<?php

namespace App\Domain\TopRated;

use Illuminate\Support\Facades\DB;

class DbQueries
{
    /**
     * @param $maxRank
     * @param $mode
     * @return mixed
     */
    public function getList($maxRank = null, $mode = null)
    {
        $games = DB::table('game_rank_alltime')
            ->join('games', 'game_rank_alltime.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*', 'games.id AS game_id',
                'categories.name AS category_name',
                'game_rank_alltime.game_rank');

        if ($maxRank) {
            $games = $games->where('game_rank_alltime.game_rank', '<=', $maxRank);
        }

        if ($mode == 'random-one') {
            $games = $games->orderBy(DB::raw('RAND()'))->limit(1);
            return $games->first();
        } else {
            $games = $games
                ->orderBy('game_rank_alltime.game_rank')
                ->orderBy('games.review_count', 'desc');
            return $games->get();
        }
    }

    public function getRandomFromTop100()
    {
        return $this->getList(100, 'random-one'); //->inRandomOrder()->first();
    }
}