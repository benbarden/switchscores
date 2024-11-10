<?php

namespace App\Domain\UserGamesCollection;

use App\Models\UserGamesCollection;

class Stats
{
    public function countAllCollections()
    {
        return UserGamesCollection::count();
    }
}