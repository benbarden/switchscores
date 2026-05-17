<?php


namespace App\Domain\GameSeries;

use App\Models\GameSeries;
use App\Models\Game;
use App\Models\Console;

class Repository
{
    public function create($name, $linkTitle, $introDescription = null, $metaDescription = null): GameSeries
    {
        return GameSeries::create([
            'series' => $name,
            'link_title' => $linkTitle,
            'intro_description' => $introDescription,
            'meta_description' => $metaDescription,
        ]);
    }

    public function edit(GameSeries $collection, $name, $linkTitle, $introDescription = null, $metaDescription = null)
    {
        $values = [
            'series' => $name,
            'link_title' => $linkTitle,
            'intro_description' => $introDescription,
            'meta_description' => $metaDescription,
        ];

        $collection->fill($values);
        $collection->save();
    }

    public function editCategoryHints(GameSeries $series, array $hints): void
    {
        $series->category_hints = empty($hints) ? null : $hints;
        $series->save();
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

    public function getAllWithGames(Console $console)
    {
        if ($console->id == Console::ID_SWITCH_2) {
            return GameSeries::whereHas('gamesSwitch2')->orderBy('series', 'asc')->get();
        } else {
            return GameSeries::whereHas('gamesSwitch1')->orderBy('series', 'asc')->get();
        }
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
            ->active()
            ->where('is_low_quality', 0)
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
            ->active()
            ->where('is_low_quality', 0)
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
            ->delisted()
            ->orderBy('title', 'asc')
            ->orderBy('eu_release_date', 'asc');

        if ($limit) {
            $games = $games->limit($limit);
        }

        return $games->get();

    }
    public function lowQualityBySeries($consoleId, $seriesId, $limit = null)
    {
        $games = Game::where('console_id', $consoleId)
            ->where('series_id', $seriesId)
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

    public function rankedBySeriesMerged($seriesId, $consoleId = null, $limit = null)
    {
        $games = Game::where('series_id', $seriesId)
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

    public function hiddenGemsBySeriesMerged($seriesId, $consoleId = null, $limit = null)
    {
        $games = Game::where('series_id', $seriesId)
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

    public function getSnapshotStatsBySeriesMerged($seriesId, $consoleId = null): array
    {
        $games = Game::where('series_id', $seriesId);

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

    public function listBySeriesMerged($seriesId, $page, $perPage, $filter, $sort, $consoleId = null)
    {
        $query = Game::where('series_id', $seriesId)
            ->active()
            ->where('is_low_quality', 0)
            ->where('eu_is_released', 1);

        if ($consoleId) {
            $query->where('console_id', $consoleId);
        }

        switch ($filter) {
            case 'ranked':    $query->where('review_count', '>=', 3); break;
            case 'hidden':    $query->whereIn('review_count', [1, 2]); break;
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