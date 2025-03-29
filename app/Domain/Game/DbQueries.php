<?php

namespace App\Domain\Game;

use Illuminate\Support\Facades\DB;

class DbQueries
{
    public function getAll()
    {
        return DB::table('games')->select('games.*')->orderBy('games.title', 'asc')->get();
    }
}