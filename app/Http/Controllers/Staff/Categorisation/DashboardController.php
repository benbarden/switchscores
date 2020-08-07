<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\Services\DataQuality\QualityStats;
use App\Services\Migrations\Category as MigrationsCategory;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Categorisation dashboard';

        $serviceQualityStats = new QualityStats();
        $serviceMigrationsCategory = new MigrationsCategory();

        $serviceGame = $this->getServiceGame();
        $serviceGameFilterList = $this->getServiceGameFilterList();
        $serviceGameGenre = $this->getServiceGameGenre();
        $serviceGameSeries = $this->getServiceGameSeries();
        $serviceTag = $this->getServiceTag();
        $serviceGameTag = $this->getServiceGameTag();

        $bindings = [];

        // Used in several calculations below
        $totalGameCount = $serviceGame->getCount();

        // Game stats: Categories
        $statsWithCategory = $serviceQualityStats->countGamesWithCategory();
        $statsWithoutCategory = $serviceQualityStats->countGamesWithoutCategory();
        $bindings['StatsWithCategory'] = $statsWithCategory;
        $bindings['StatsWithoutCategory'] = $statsWithoutCategory;
        $statsCategoryProgress = ($statsWithCategory) / $totalGameCount * 100;
        $bindings['StatsCategoryProgress'] = round($statsCategoryProgress, 2);

        // Game stats: Tags
        $missingTags = $serviceGameFilterList->getGamesWithoutTags();
        $statsWithoutTags = count($missingTags);
        $statsWithTags = $totalGameCount - $statsWithoutTags;
        $bindings['StatsWithoutTags'] = $statsWithoutTags;
        $bindings['StatsWithTags'] = $statsWithTags;
        $statsTagsProgress = ($statsWithTags) / $totalGameCount * 100;
        $bindings['StatsTagsProgress'] = round($statsTagsProgress, 2);

        // Game stats: Series
        $statsWithSeries = $serviceQualityStats->countGamesWithSeries();
        $statsWithoutSeries = $serviceQualityStats->countGamesWithoutSeries();
        $bindings['StatsWithSeries'] = $statsWithSeries;
        $bindings['StatsWithoutSeries'] = $statsWithoutSeries;

        // Genres
        $missingGenres = $serviceGameGenre->getGamesWithoutGenres();
        $bindings['MissingGenresCount'] = count($missingGenres);

        // No category or tag
        $missingCategoriesAndTags = $serviceGameFilterList->getGamesWithoutCategoriesOrTags();
        $bindings['NoCategoryOrTagCount'] = count($missingCategoriesAndTags);

        // Migrations: Category
        $bindings['NoCategoryOneGenreCount'] = $serviceMigrationsCategory->countGamesWithOneGenre();
        $bindings['NoCategoryPuzzleAndOneOtherGenre'] = $serviceMigrationsCategory->countGamesWithNamedGenreAndOneOther('Puzzle');
        $bindings['AllGamesWithNoCategoryCount'] = $serviceMigrationsCategory->countGamesWithNoCategory();

        // Title matches: Series
        $seriesList = $serviceGameSeries->getAll();

        $seriesArray = [];

        foreach ($seriesList as $series) {

            $seriesId = $series->id;
            $seriesName = $series->series;
            $seriesLink = $series->link_title;

            $gameSeriesList = $serviceGame->getSeriesTitleMatch($seriesName);
            $gameCount = count($gameSeriesList);

            if ($gameCount > 0) {

                $seriesArray[] = [
                    'id' => $seriesId,
                    'name' => $seriesName,
                    'link' => $seriesLink,
                    'gameCount' => count($gameSeriesList),
                ];

            }

        }

        $bindings['GameSeriesMatchList'] = $seriesArray;

        // Title matches: Tags
        $tagList = $serviceTag->getAll();

        $tagArray = [];

        foreach ($tagList as $tag) {

            $tagId = $tag->id;
            $tagName = $tag->tag_name;
            $tagLink = $tag->link_title;

            $gameTagList = $serviceGame->getTagTitleMatch($tagName);

            if ($gameTagList) {

                $gameTagCount = 0;
                foreach ($gameTagList as $game) {
                    if (!$serviceGameTag->gameHasTag($game->id, $tagId)) {
                        $gameTagCount++;
                    }
                }

                if ($gameTagCount > 0) {

                    $tagArray[] = [
                        'id' => $tagId,
                        'name' => $tagName,
                        'link' => $tagLink,
                        'gameCount' => $gameTagCount,
                    ];

                }

            }

        }

        $bindings['GameTagMatchList'] = $tagArray;

        // Core stuff
        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        return view('staff.categorisation.dashboard', $bindings);
    }
}
