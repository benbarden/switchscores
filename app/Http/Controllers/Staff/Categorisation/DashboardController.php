<?php

namespace App\Http\Controllers\Staff\Categorisation;

use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\GameSeries;
use App\Models\Category;
use App\Services\Migrations\Category as MigrationsCategory;
use App\Tag;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;
    protected $repoGameStats;
    protected $repoGameSeries;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        GameStatsRepository $repoGameStats,
        GameSeriesRepository $repoGameSeries
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoGameStats = $repoGameStats;
        $this->repoGameSeries = $repoGameSeries;
    }

    private function getCategoryMatchesStats()
    {
        $categoryList = $this->getServiceCategory()->getAll();

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
        $tagList = $this->getServiceTag()->getAll();

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
        $bindings = $this->getBindings('Categorisation dashboard');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Categorisation dashboard');

        $serviceMigrationsCategory = new MigrationsCategory();

        // Migrations: Category
        $bindings['NoCategoryOneGenreCount'] = $serviceMigrationsCategory->countGamesWithOneGenre();
        $bindings['NoCategoryPuzzleAndOneOtherGenre'] = $serviceMigrationsCategory->countGamesWithNamedGenreAndOneOther('Puzzle');
        $bindings['NoCategoryAllGamesCount'] = $serviceMigrationsCategory->countGamesWithNoCategory();
        $bindings['NoCategoryEshopDataCount'] = count($serviceMigrationsCategory->getGamesWithEshopDataAndNoCategory());
        $bindings['NoTagCount'] = $this->repoGameStats->totalUntagged();

        // Title matches
        $bindings['GameSeriesMatchList'] = $this->getSeriesMatchesStats();

        return view('staff.categorisation.dashboard', $bindings);
    }

    public function categoryTitleMatch(Category $category)
    {
        $pageTitle = 'Category matches: '.$category->name;

        $bindings = $this->getBindings($pageTitle);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getCategoryTitleMatch($category->name);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function seriesTitleMatch(GameSeries $gameSeries)
    {
        $pageTitle = 'Series matches: '.$gameSeries->series;

        $bindings = $this->getBindings($pageTitle);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getSeriesTitleMatch($gameSeries->series);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function tagTitleMatch(Tag $tag)
    {
        $pageTitle = 'Tag matches: '.$tag->tag_name;

        $bindings = $this->getBindings($pageTitle);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getTagTitleMatch($tag);

        return view('staff.games.list.standard-view', $bindings);
    }
}
