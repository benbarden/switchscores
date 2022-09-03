<?php


namespace App\Domain\GamesCompany;

use App\Models\Partner;

class Repository
{
    public function getAll()
    {
        return Partner::orderBy('name', 'asc')->get();
    }

    public function normalQuality()
    {
        return Partner::where('is_low_quality', 0)->orderBy('name', 'asc')->get();
    }

    public function lowQuality()
    {
        return Partner::where('is_low_quality', 1)->orderBy('name', 'asc')->get();
    }

    public function normalQualityCount()
    {
        return Partner::where('is_low_quality', 0)->orderBy('name', 'asc')->count();
    }

    public function lowQualityCount()
    {
        return Partner::where('is_low_quality', 1)->orderBy('name', 'asc')->count();
    }

    public function find($id)
    {
        return Partner::find($id);
    }

    public function searchGamesCompany($name)
    {
        return Partner::where('name', 'LIKE', '%'.$name.'%')->orderBy('name', 'asc')->get();
    }
}