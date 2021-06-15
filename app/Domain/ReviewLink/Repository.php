<?php


namespace App\Domain\ReviewLink;

use App\ReviewLink;

class Repository
{
    public function byGame($gameId)
    {
        return ReviewLink::where('game_id', $gameId)->get();
    }
}