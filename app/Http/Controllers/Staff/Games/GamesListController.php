<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\Tag\Repository as TagRepository;

use App\Models\Category;
use App\Models\GameSeries;
use App\Models\Tag;

class GamesListController extends Controller
{
    public function __construct(
        private GameListsRepository $repoGameLists,
        private CategoryRepository $repoCategory,
        private GameSeriesRepository $repoGameSeries,
        private TagRepository $repoTag
    )
    {
    }

    public function gamesToRelease()
    {
        $pageTitle = 'Games to release';
        $tableSort = "[ 6, 'asc'], [ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->gamesForRelease();

        $bindings['CustomHeader'] = 'Action';
        $bindings['ListMode'] = 'games-to-release';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyAdded()
    {
        $pageTitle = 'Recently added';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->recentlyAdded(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyReleased()
    {
        $pageTitle = 'Recently released';
        $tableSort = "[ 6, 'desc'], [ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->recentlyReleasedAll(1, 100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingGames()
    {
        $pageTitle = 'Upcoming and unreleased';
        $tableSort = "[ 6, 'asc'], [ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->upcomingAll();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingEshopCrosscheck()
    {
        $pageTitle = 'Upcoming (eShop crosscheck)';
        $tableSort = "[ 6, 'asc'], [ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        //$bindings['GameList'] = $this->repoGameLists->upcomingEshopCrosscheck();
        $bindings['GameListNoDate'] = $this->repoGameLists->upcomingEshopCrosscheckNoDate();

        return view('staff.games.list.upcoming-eshop-crosscheck', $bindings);
    }

    public function noCategoryExcludingLowQuality()
    {
        $pageTitle = 'No category (Excluding low quality)';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noCategoryExcludingLowQuality();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noCategoryAll()
    {
        $pageTitle = 'No category (All)';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noCategoryAll();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noCategoryWithCollection()
    {
        $pageTitle = 'No category with collection';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noCategoryWithCollection();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noCategoryWithReviews()
    {
        $pageTitle = 'No category with reviews';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noCategoryWithReviews();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEuReleaseDate()
    {
        $pageTitle = 'No EU release date';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noEuReleaseDate();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEshopPrice()
    {
        $pageTitle = 'No eShop price';
        $tableSort = "[ 6, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noPrice();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noVideoType()
    {
        $pageTitle = 'No video type';
        $tableSort = "[ 0, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noVideoType();
        $bindings['ListLimit'] = "200";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noAmazonUkLink()
    {
        $pageTitle = 'No Amazon UK link';
        $tableSort = "[ 6, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $listLimit = 1000;
        $bindings['GameList'] = $this->repoGameLists->noAmazonUkLink($listLimit);
        $bindings['ListLimit'] = $listLimit;

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noAmazonUsLink()
    {
        $pageTitle = 'No Amazon US link';
        $tableSort = "[ 6, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $listLimit = 1000;
        $bindings['GameList'] = $this->repoGameLists->noAmazonUsLink($listLimit);
        $bindings['ListLimit'] = $listLimit;

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noNintendoCoUkLink()
    {
        $pageTitle = 'No Nintendo.co.uk link, and no override URL';
        $tableSort = "[ 6, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noNintendoCoUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function brokenNintendoCoUkLink()
    {
        $pageTitle = 'Broken Nintendo.co.uk link';
        $tableSort = "[ 4, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->brokenNintendoCoUkLink();

        $bindings['CustomHeader'] = 'Review count';
        $bindings['ListMode'] = 'broken-nintendo-co-uk-link';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function formatOptionList($format, $value = null)
    {
        if ($value == null) {
            $valueDesc = '(Not set)';
        } else {
            $valueDesc = $value;
        }
        $pageTitle = 'By format option: '.$format.' - '.$valueDesc;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->formatOption($format, $value);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byCategory(Category $category)
    {
        $pageTitle = 'By category: '.$category->name;
        $tableSort = "[ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoCategory->gamesByCategory($category->id);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function bySeries(GameSeries $gameSeries)
    {
        $pageTitle = 'By series: '.$gameSeries->series;
        $tableSort = "[ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameSeries->gamesBySeries(null, $gameSeries->id);

        $bindings['CustomHeader'] = 'Series';
        $bindings['ListMode'] = 'by-series';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byTag(Tag $tag)
    {
        $pageTitle = 'By tag: '.$tag->tag_name;
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoTag->gamesByTag($tag->id);

        return view('staff.games.list.standard-view', $bindings);
    }
}