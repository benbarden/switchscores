<?php


namespace App\Domain\Partner;

use App\Models\Partner;

class Repository
{
    /**
     * @param $id
     * @return \App\Models\Partner
     */
    public function find($id)
    {
        return Partner::find($id);
    }

    public function searchGamesCompany($name)
    {
        return Partner::where('name', 'LIKE', '%'.$name.'%')
            ->where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function gamesCompanies()
    {
        return Partner::where('type_id', Partner::TYPE_GAMES_COMPANY)
            ->orderBy('name', 'asc')
            ->get();
    }
}