<?php

namespace App\Domain\TopRated;

use App\Models\Game;

class Repository
{
    public function getListByConsole($consoleId, $minRank = null, $maxRank = null, $mode = null)
    {
        $games = Game::query()
            ->join('game_rank_alltime', 'game_rank_alltime.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*')
            ->addSelect([
                'game_rank_alltime.game_rank',
                'categories.name as category_name',
            ])
            ->where('games.console_id', $consoleId);

        if ($minRank && $maxRank) {
            $games->whereBetween('game_rank_alltime.game_rank', [$minRank, $maxRank]);
        }

        if ($mode === 'random-one') {
            return $games->inRandomOrder()->first();
        }

        return $games
            ->orderBy('game_rank_alltime.game_rank')
            ->orderBy('games.review_count', 'desc')
            ->get();
    }

    public function byConsoleAndYear($consoleId, $year, $limit = null)
    {
        $query = Game::query()
            ->join('game_rank_year', 'game_rank_year.game_id', '=', 'games.id')
            ->leftJoin('categories', 'games.category_id', '=', 'categories.id')
            ->select('games.*')
            ->addSelect([
                'categories.name as category_name',
                'game_rank_year.game_rank',
            ])
            ->where('games.console_id', $consoleId)
            ->where('game_rank_year.release_year', $year)
            ->orderBy('game_rank_year.game_rank')
            ->orderBy('games.review_count', 'desc');

        if (!is_null($limit)) {
            $query->limit($limit);
        }

        return $query->get();
    }
}