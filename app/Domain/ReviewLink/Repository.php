<?php


namespace App\Domain\ReviewLink;

use App\ReviewLink;

class Repository
{
    public function byGame($gameId)
    {
        return ReviewLink::where('game_id', $gameId)->get();
    }

    public function byGameAndSite($gameId, $siteId)
    {
        return ReviewLink::where('game_id', $gameId)->where('site_id', $siteId)->first();
    }

}