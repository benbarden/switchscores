<?php


namespace App\Domain\GameSearch;

use App\Game;

class Builder
{
    public function build($params, &$bindings)
    {
        $paramsEntered = 0;

        if (array_key_exists('search_keywords', $params)) {
            $title = $params['search_keywords'];
            if ($title != '') {
                $paramsEntered++;
            }
        } else {
            $title = null;
        }
        if (array_key_exists('search_score_minimum', $params)) {
            $scoreMinimum = $params['search_score_minimum'];
            $paramsEntered++;
        } else {
            $scoreMinimum = null;
        }
        if ($scoreMinimum) {
            $showRankedUnranked = 'Ranked';
        } elseif (array_key_exists('search_ranked_unranked', $params)) {
            $showRankedUnranked = $params['search_ranked_unranked'];
            $paramsEntered++;
        } else {
            $showRankedUnranked = null;
        }
        if (array_key_exists('search_price_maximum', $params)) {
            $priceMaximum = $params['search_price_maximum'];
            $paramsEntered++;
        } else {
            $priceMaximum = null;
        }
        if (array_key_exists('search_category', $params)) {
            $categoryId = $params['search_category'];
            $paramsEntered++;
        } else {
            $categoryId = null;
        }
        if (array_key_exists('search_series', $params)) {
            $seriesId = $params['search_series'];
            $paramsEntered++;
        } else {
            $seriesId = null;
        }
        if (array_key_exists('search_collection', $params)) {
            $collectionId = $params['search_collection'];
            $paramsEntered++;
        } else {
            $collectionId = null;
        }

        if ($paramsEntered == 0) return null;

        // Re-populate form
        $bindings['SearchKeywords'] = $title;
        $bindings['SearchScoreMinimum'] = $scoreMinimum;
        $bindings['SearchShowRankedUnranked'] = $showRankedUnranked;
        $bindings['SearchPriceMaximum'] = $priceMaximum;
        $bindings['SearchCategoryId'] = $categoryId;
        $bindings['SearchSeriesId'] = $seriesId;
        $bindings['SearchCollectionId'] = $collectionId;

        $searchResults = Game::searchTitle($title)
            ->searchShowRankedUnranked($showRankedUnranked)
            ->searchScoreMinimum($scoreMinimum)
            ->searchPriceMaximum($priceMaximum)
            ->searchCategoryId($categoryId)
            ->searchSeriesId($seriesId)
            ->searchCollectionId($collectionId);

        // Hide de-listed
        if (!$title) {
            $searchResults = $searchResults->where('format_digital', '<>', Game::FORMAT_DELISTED);
        }

        if ($showRankedUnranked == 'Ranked') {
            $searchResults = $searchResults->orderBy('game_rank', 'asc');
            $bindings['OrderingBlurb'] = 'Showing highest ranked games first.';
        } else {
            $searchResults = $searchResults->orderBy('eu_release_date', 'desc');
            $bindings['OrderingBlurb'] = 'Showing newest and upcoming releases first.';
        }

        $searchResults = $searchResults->limit(200)->get();

        return $searchResults;
    }
}