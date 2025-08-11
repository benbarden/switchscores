<?php


namespace App\Domain\GameCollection;

use App\Models\Game;
use App\Models\GameCollection;

class Repository
{
    public function create($name, $linkTitle)
    {
        GameCollection::create([
            'name' => $name,
            'link_title' => $linkTitle,
        ]);
    }

    public function edit(GameCollection $collection, $name, $linkTitle)
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
        ];

        $collection->fill($values);
        $collection->save();
    }

    public function delete($id)
    {
        GameCollection::where('id', $id)->delete();
    }

    public function find($id)
    {
        return GameCollection::find($id);
    }

    public function getAll()
    {
        return GameCollection::orderBy('name', 'asc')->get();
    }

    public function getByName($name)
    {
        return GameCollection::where('name', $name)->first();
    }

    public function getByLinkTitle($linkTitle)
    {
        return GameCollection::where('link_title', $linkTitle)->first();
    }

    public function gamesByCollection($collectionId)
    {
        return Game::where('collection_id', $collectionId)->orderBy('title', 'asc')->get();
    }

    public function rankedByCollection($consoleId, $collectionId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->orderBy('game_rank', 'asc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function unrankedByCollection($consoleId, $collectionId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->whereNull('game_rank')
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 0)
            ->orderBy('review_count', 'desc')
            ->orderBy('title', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function delistedByCollection($consoleId, $collectionId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('collection_id', $collectionId)
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

    public function lowQualityByCollection($consoleId, $collectionId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->where('format_digital', '<>', Game::FORMAT_DELISTED)
            ->where('is_low_quality', 1)
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }
}