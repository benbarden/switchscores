<?php


namespace App\Domain\GameSearch;

use App\Domain\Category\Repository as CategoryRepository;
use App\Models\Game;

class Builder
{
    private $repoCategory;

    public function __construct(
        CategoryRepository $repoCategory
    )
    {
        $this->repoCategory = $repoCategory;
    }

    public function build($params, &$bindings)
    {
        $paramsEntered = 0;

        $title = null;
        $yearReleased = null;
        $scoreMinimum = null;
        $priceMaximum = null;
        $categoryId = null;
        $seriesId = null;
        $collectionId = null;

        if (array_key_exists('search_keywords', $params)) {
            $title = $params['search_keywords'];
            if ($title != '') {
                $paramsEntered++;
            }
        }
        if (array_key_exists('search_year_released', $params)) {
            if ($params['search_year_released']) {
                $yearReleased = (int) $params['search_year_released'];
                $paramsEntered++;
            }
        }
        if (array_key_exists('search_score_minimum', $params)) {
            if ($params['search_score_minimum']) {
                $scoreMinimum = (float) $params['search_score_minimum'];
                $paramsEntered++;
            }
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
            if ($params['search_price_maximum']) {
                $priceMaximum = (float) $params['search_price_maximum'];
                $paramsEntered++;
            }
        }
        if (array_key_exists('search_category', $params)) {
            if ($params['search_category']) {
                $categoryId = (int) $params['search_category'];
                $paramsEntered++;
            }
        }
        if (array_key_exists('search_series', $params)) {
            if ($params['search_series']) {
                $seriesId = (int) $params['search_series'];
                $paramsEntered++;
            }
        }
        if (array_key_exists('search_collection', $params)) {
            if ($params['search_collection']) {
                $collectionId = (int) $params['search_collection'];
                $paramsEntered++;
            }
        }

        if ($paramsEntered == 0) return null;

        // Category id list
        if ($categoryId) {

            $categoryIdList = [$categoryId];
            $category = $this->repoCategory->find($categoryId);
            if ($category) {
                if ($category->children) {
                    foreach ($category->children as $child) {
                        $categoryIdList[] = $child->id;
                    }
                }
            }

        } else {

            $categoryIdList = null;

        }

        // Re-populate form
        $bindings['SearchKeywords'] = $title;
        $bindings['SearchShowRankedUnranked'] = $showRankedUnranked;
        $bindings['SearchYearReleased'] = $yearReleased;
        $bindings['SearchScoreMinimum'] = $scoreMinimum;
        $bindings['SearchPriceMaximum'] = $priceMaximum;
        $bindings['SearchCategoryId'] = $categoryId;
        $bindings['SearchSeriesId'] = $seriesId;
        $bindings['SearchCollectionId'] = $collectionId;

        $searchResults = Game::searchTitle($title)
            ->searchShowRankedUnranked($showRankedUnranked)
            ->searchYearReleased($yearReleased)
            ->searchScoreMinimum($scoreMinimum)
            ->searchPriceMaximum($priceMaximum)
            ->searchCategoryId($categoryIdList)
            ->searchSeriesId($seriesId)
            ->searchCollectionId($collectionId);

        if ($title == 'the') abort(404);

        // Hide de-listed and soft-deleted
        if (!$title) {
            $searchResults = $searchResults->active();
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