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

    public function gamesBySeries($consoleId, $seriesId)
    {
        $gameList = Game::where('series_id', $seriesId);
        if ($consoleId != null) {
            $gameList = $gameList->where('console_id', $consoleId);
        }
        $gameList = $gameList->orderBy('title', 'asc')->get();
        return $gameList;
    }

    public function rankedBySeries($consoleId, $seriesId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('series_id', $seriesId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('game_rank', 'asc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function unrankedBySeries($consoleId, $seriesId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('series_id', $seriesId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->orderBy('review_count', 'desc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function delistedBySeries($consoleId, $seriesId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('series_id', $seriesId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '=', Game::FORMAT_DELISTED)
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }
}