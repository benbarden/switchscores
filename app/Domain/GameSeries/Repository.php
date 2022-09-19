<?php


namespace App\Domain\GameSeries;

use App\Models\GameSeries;
use App\Models\Game;

class Repository
{
    public function create($name, $linkTitle)
    {
        GameSeries::create([
            'series' => $name,
            'link_title' => $linkTitle
        ]);
    }

    public function edit(GameSeries $collection, $name, $linkTitle)
    {
        $values = [
            'series' => $name,
            'link_title' => $linkTitle,
        ];

        $collection->fill($values);
        $collection->save();
    }

    public function find($seriesId)
    {
        return GameSeries::find($seriesId);
    }

    public function delete($seriesId)
    {
        GameSeries::where('id', $seriesId)->delete();
    }

    public function getAll()
    {
        return GameSeries::orderBy('series', 'asc')->get();
    }

    public function getByName($name)
    {
        return GameSeries::where('series', $name)->first();
    }

    public function getByLinkTitle($linkTitle)
    {
        return GameSeries::where('link_title', $linkTitle)->first();
    }

    public function gamesBySeries($seriesId)
    {
        return Game::where('series_id', $seriesId)->orderBy('title', 'asc')->get();
    }

}