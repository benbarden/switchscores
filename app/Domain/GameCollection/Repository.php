<?php


namespace App\Domain\GameCollection;

use App\Models\Game;
use App\Models\GameCollection;

class Repository
{
    public function create($name, $linkTitle, $introDescription = null, $metaDescription = null): GameCollection
    {
        return GameCollection::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'intro_description' => $introDescription,
            'meta_description' => $metaDescription,
        ]);
    }

    public function edit(GameCollection $collection, $name, $linkTitle, $introDescription = null, $metaDescription = null)
    {
        $values = [
            'name' => $name,
            'link_title' => $linkTitle,
            'intro_description' => $introDescription,
            'meta_description' => $metaDescription,
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
            ->active()
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
            ->active()
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
            ->delisted()
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
            ->active()
            ->where('is_low_quality', 1)
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }

    public function rankedByCollectionMerged($collectionId, $consoleId = null, $limit = null)
    {
        $games = Game::where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->whereNotNull('game_rank')
            ->active()
            ->where('is_low_quality', 0)
            ->orderBy('game_rank', 'asc')
            ->orderBy('title', 'asc');

        if ($consoleId) {
            $games->where('console_id', $consoleId);
        }

        if ($limit) {
            $games->limit($limit);
        }

        return $games->get();
    }

    public function hiddenGemsByCollectionMerged($collectionId, $consoleId = null, $limit = null)
    {
        $games = Game::where('collection_id', $collectionId)
            ->where('eu_is_released', 1)
            ->whereIn('review_count', [1, 2])
            ->active()
            ->where('is_low_quality', 0)
            ->orderBy('rating_avg', 'desc');

        if ($consoleId) {
            $games->where('console_id', $consoleId);
        }

        if ($limit) {
            $games->limit($limit);
        }

        return $games->get();
    }

    public function getSnapshotStatsByCollectionMerged($collectionId, $consoleId = null): array
    {
        $games = Game::where('collection_id', $collectionId);

        if ($consoleId) {
            $games->where('console_id', $consoleId);
        }

        $total               = (clone $games)->active()->where('is_low_quality', 0)->where('eu_is_released', 1)->count();
        $ranked              = (clone $games)->where('review_count', '>=', 3)->active()->where('is_low_quality', 0)->where('eu_is_released', 1)->count();
        $reviewedButUnranked = (clone $games)->whereIn('review_count', [1, 2])->active()->where('is_low_quality', 0)->where('eu_is_released', 1)->count();
        $noReviews           = (clone $games)->where('review_count', 0)->active()->where('is_low_quality', 0)->where('eu_is_released', 1)->count();

        return [
            'total'               => $total,
            'ranked'              => $ranked,
            'reviewedButUnranked' => $reviewedButUnranked,
            'noReviews'           => $noReviews,
        ];
    }

    public function listByCollectionMerged($collectionId, $page, $perPage, $filter, $sort, $consoleId = null)
    {
        $query = Game::where('collection_id', $collectionId)
            ->active()
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1);

        if ($consoleId) {
            $query->where('console_id', $consoleId);
        }

        switch ($filter) {
            case 'ranked':   $query->where('review_count', '>=', 3); break;
            case 'hidden':   $query->whereIn('review_count', [1, 2]); break;
            case 'noreviews': $query->where('review_count', 0); break;
        }

        switch ($sort) {
            case 'title_asc':    $query->orderBy('title', 'asc'); break;
            case 'title_desc':   $query->orderBy('title', 'desc'); break;
            case 'rating_desc':  $query->orderBy('rating_avg', 'desc'); break;
            case 'rating_asc':   $query->orderBy('rating_avg', 'asc'); break;
            case 'release_desc': $query->orderBy('eu_release_date', 'desc'); break;
            case 'release_asc':  $query->orderBy('eu_release_date', 'asc'); break;
        }

        $total = $query->count();
        $pages = max((int) ceil($total / $perPage), 1);

        if ($page > $pages) {
            $page = $pages;
        }

        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }
}