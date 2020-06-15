<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\GameSeries;


class GameSeriesService
{
    public function create($name, $linkTitle)
    {
        GameSeries::create([
            'series' => $name,
            'link_title' => $linkTitle
        ]);
    }

    public function find($seriesId)
    {
        return GameSeries::find($seriesId);
    }

    public function delete($seriesId)
    {
        GameSeries::where('id', $seriesId)->delete();
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        $seriesList = GameSeries::
            orderBy('series', 'asc')
            ->get();
        return $seriesList;
    }

    public function getByName($name)
    {
        $series = GameSeries::
            where('series', $name)
            ->first();
        return $series;
    }

    public function getByLinkTitle($linkTitle)
    {
        return GameSeries::where('link_title', $linkTitle)->first();
    }
}