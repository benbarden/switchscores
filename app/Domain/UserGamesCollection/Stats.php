<?php

namespace App\Domain\UserGamesCollection;

use App\Models\UserGamesCollection;

class Stats
{
    public function countAllCollections()
    {
        return UserGamesCollection::count();
    }

    public function totalByUser($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->count();
    }
}