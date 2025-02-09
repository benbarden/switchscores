<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameLists\MissingCategory as GameListMissingCategoryRepository;

use App\Models\Category;
use App\Models\GameSeries;
use App\Models\Tag;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameStatsRepository $repoGameStats,
        private GameSeriesRepository $repoGameSeries,
        private TagRepository $repoTag,
        private CategoryRepository $repoCategory,
        private GameListMissingCategoryRepository $repoGameListMissingCategory,
    )
    {
    }

    private function getCategoryMatchesStats()
    {
        $categoryList = $this->repoCategory->getAll();

        $categoryArray = [];

        foreach ($categoryList as $category) {

            $categoryId = $category->id;
            $categoryName = $category->name;
            $categoryLink = $category->link_title;

            $gameCategoryList = $this->getServiceGame()->getCategoryTitleMatch($categoryName);
            $gameCount = count($gameCategoryList);

            if ($gameCount > 0) {
                $categoryArray[] = [
                    'id' => $categoryId,
                    'name' => $categoryName,
                    'link' => $categoryLink,
                    'gameCount' => $gameCount,
                    'category' => $category,
                ];
            }

        }

        return $categoryArray;
    }

    private function getSeriesMatchesStats()
    {
        $seriesList = $this->repoGameSeries->getAll();

        $seriesArray = [];

        foreach ($seriesList as $series) {

            $seriesId = $series->id;
            $seriesName = $series->series;
            $seriesLink = $series->link_title;

            $gameSeriesList = $this->getServiceGame()->getSeriesTitleMatch($seriesName);
            $gameCount = count($gameSeriesList);

            if ($gameCount > 0) {
                $seriesArray[] = [
                    'id' => $seriesId,
                    'name' => $seriesName,
                    'link' => $seriesLink,
                    'gameCount' => $gameCount,
                    'series' => $series,
                ];
            }

        }

        return $seriesArray;
    }

    private function getTagMatchesStats()
    {
        $tagList = $this->repoTag->getAll();

        $tagArray = [];

        foreach ($tagList as $tag) {

            $tagId = $tag->id;
            $tagName = $tag->tag_name;
            $tagLink = $tag->link_title;

            $gameTagList = $this->getServiceGame()->getTagTitleMatch($tag);
            $gameCount = count($gameTagList);

            if ($gameCount > 0) {
                $tagArray[] = [
                    'id' => $tagId,
                    'name' => $tagName,
                    'link' => $tagLink,
                    'gameCount' => $gameCount,
                    'tag' => $tag,
                ];
            }

        }

        return $tagArray;
    }

    public function show()
    {
        $pageTitle = 'Categorisation dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Migrations: Category
        $bindings['NoCategoryCount'] = $this->repoGameStats->totalNoCategoryAll();

        // Bulk edit stats
        $missingCategorySimulation = $this->repoGameListMissingCategory->simulation();
        $bindings['BulkEditMissingCategorySimCount'] = count($missingCategorySimulation);
        $missingCategorySurvival = $this->repoGameListMissingCategory->survival();
        $bindings['BulkEditMissingCategorySurvivalCount'] = count($missingCategorySurvival);
        $missingCategoryQuiz = $this->repoGameListMissingCategory->quiz();
        $bindings['BulkEditMissingCategoryQuizCount'] = count($missingCategoryQuiz);
        $missingCategorySpotTheDifference = $this->repoGameListMissingCategory->spotTheDifference();
        $bindings['BulkEditMissingCategorySpotTheDifferenceCount'] = count($missingCategorySpotTheDifference);
        $missingCategoryPuzzle = $this->repoGameListMissingCategory->puzzle();
        $bindings['BulkEditMissingCategoryPuzzleCount'] = count($missingCategoryPuzzle);
        $missingCategorySportsRacing = $this->repoGameListMissingCategory->sportsAndRacing();
        $bindings['BulkEditMissingCategorySportsRacingCount'] = count($missingCategorySportsRacing);

        return view('staff.categorisation.dashboard', $bindings);
    }

    public function categoryTitleMatch(Category $category)
    {
        $pageTitle = 'Category matches: '.$category->name;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getCategoryTitleMatch($category->name);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function seriesTitleMatch(GameSeries $gameSeries)
    {
        $pageTitle = 'Series matches: '.$gameSeries->series;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getSeriesTitleMatch($gameSeries->series);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function tagTitleMatch(Tag $tag)
    {
        $pageTitle = 'Tag matches: '.$tag->tag_name;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getTagTitleMatch($tag);

        return view('staff.games.list.standard-view', $bindings);
    }
}
