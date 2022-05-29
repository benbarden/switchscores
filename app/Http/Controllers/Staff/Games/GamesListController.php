<?php

namespace App\Http\Controllers\Staff\Games;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Models\Category;
use App\Models\GameSeries;
use App\Models\Tag;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class GamesListController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $repoGameLists;

    public function __construct(
        GameListsRepository $repoGameLists
    )
    {
        $this->repoGameLists = $repoGameLists;
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

        $bindings['GameList'] = $this->getServiceGame()->getRecentlyReleased(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingGames()
    {
        $pageTitle = 'Upcoming and unreleased';
        $tableSort = "[ 6, 'asc'], [ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllUnreleased();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingEshopCrosscheck()
    {
        $pageTitle = 'Upcoming (eShop crosscheck)';
        $tableSort = "[ 6, 'asc'], [ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->upcomingEshopCrosscheck();
        $bindings['GameListNoDate'] = $this->repoGameLists->upcomingEshopCrosscheckNoDate();

        return view('staff.games.list.upcoming-eshop-crosscheck', $bindings);
    }

    public function noCategory()
    {
        $pageTitle = 'No category';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noCategory();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noTag()
    {
        $pageTitle = 'No tag';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoGameLists->noTag();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEuReleaseDate()
    {
        $pageTitle = 'No EU release date';
        $tableSort = "[ 0, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllWithNoEuReleaseDate();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEshopPrice()
    {
        $pageTitle = 'No eShop price';
        $tableSort = "[ 6, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getWithoutPrices();

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
        $tableSort = "[ 0, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getWithNoAmazonUkLink();
        $bindings['ListLimit'] = "200";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noNintendoCoUkLink()
    {
        $pageTitle = 'No Nintendo.co.uk link';
        $tableSort = "[ 6, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getWithNoNintendoCoUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function brokenNintendoCoUkLink()
    {
        $pageTitle = 'Broken Nintendo.co.uk link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getWithBrokenNintendoCoUkLink();

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

        $bindings['GameList'] = $this->getServiceGame()->getByCategory($category);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function bySeries(GameSeries $gameSeries)
    {
        $pageTitle = 'By series: '.$gameSeries->series;
        $tableSort = "[ 1, 'asc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->getServiceGame()->getBySeries($gameSeries);

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

        $bindings['GameList'] = $this->getServiceGame()->getByTag($tag->id);

        return view('staff.games.list.standard-view', $bindings);
    }
}