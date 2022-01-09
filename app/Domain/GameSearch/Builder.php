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

        if (array_key_exists('search_keywords', $params)) {
            $title = $params['search_keywords'];
            if ($title != '') {
                $paramsEntered++;
            }
        } else {
            $title = null;
        }
        if (array_key_exists('search_year_released', $params)) {
            $yearReleased = $params['search_year_released'];
            $paramsEntered++;
        } else {
            $yearReleased = null;
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