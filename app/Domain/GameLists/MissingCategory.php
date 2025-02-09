<?php

namespace App\Domain\GameLists;

use App\Models\Game;

class MissingCategory
{
    public function simulation($limit = null)
    {
        $games = Game::whereNull('category_id')
            ->where('games.title', 'LIKE', '%sim%')
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('id', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function puzzle($limit = null)
    {
        $games = Game::whereNull('category_id')
            ->where(function ($query) {
                $query->where('games.title', 'LIKE', '%puzzle%')
                    ->orWhere('games.title', 'LIKE', '%soko%')
                    ->orWhere('games.title', 'LIKE', '%zumba%');
            })
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('id', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }

    public function sportsAndRacing($limit = null)
    {
        $games = Game::whereNull('category_id')
            ->where(function ($query) {
                $query->where('games.title', 'LIKE', '%bowling%')
                    ->orWhere('games.title', 'LIKE', '%snooker%')
                    ->orWhere('games.title', 'LIKE', '%tennis%')
                    ->orWhere('games.title', 'LIKE', '%football%')
                    ->orWhere('games.title', 'LIKE', '%baseball%')
                    ->orWhere('games.title', 'LIKE', '%golf%')
                    ->orWhere('games.title', 'LIKE', '%racing%');
            })
            ->orderBy('eu_release_date', 'asc')
            ->orderBy('eshop_europe_order', 'asc')
            ->orderBy('id', 'asc');

        if ($limit != null) {
            $games = $games->limit($limit);
        }
        $games = $games->get();

        return $games;
    }
}