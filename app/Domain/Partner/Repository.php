<?php


namespace App\Domain\Partner;

use App\Partner;

class Repository
{
    public function searchGamesCompany($name)
    {
        return Partner::where('name', 'LIKE', '%'.$name.'%')
            ->where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->orderBy('name', 'asc')
            ->get();
    }
}