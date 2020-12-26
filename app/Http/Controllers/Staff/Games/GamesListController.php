<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Category;
use App\GameSeries;
use App\Tag;

use App\Traits\SwitchServices;

class GamesListController extends Controller
{
    use SwitchServices;

    private function getListBindings($pageTitle, $tableSort = '')
    {
        $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeGamesSubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Games')
            ->setBreadcrumbs($breadcrumbs);

        if ($tableSort) {
            $bindings = $bindings->setDatatablesSort($tableSort);
        } else {
            $bindings = $bindings->setDatatablesSortDefault();
        }

        return $bindings->getBindings();
    }

    public function gamesToRelease()
    {
        $bindings = $this->getListBindings('Games to release', "[ 5, 'asc'], [ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getActionListGamesForRelease();

        $bindings['CustomHeader'] = 'Action';
        $bindings['ListMode'] = 'games-to-release';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyAdded()
    {
        $bindings = $this->getListBindings('Recently added');

        $bindings['GameList'] = $this->getServiceGame()->getRecentlyAdded(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyReleased()
    {
        $bindings = $this->getListBindings('Recently released', "[ 4, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getRecentlyReleased(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingGames()
    {
        $bindings = $this->getListBindings('Upcoming and unreleased', "[ 5, 'asc']");

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllUnreleased();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEuReleaseDate()
    {
        $bindings = $this->getListBindings('No EU release date', "[ 0, 'desc']");

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllWithNoEuReleaseDate();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEshopPrice()
    {
        $bindings = $this->getListBindings('No eShop price', "[ 5, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithoutPrices();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noVideoUrl()
    {
        $bindings = $this->getListBindings('No video URL', "[ 0, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithNoVideoUrl();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noAmazonUkLink()
    {
        $bindings = $this->getListBindings('No Amazon UK link', "[ 0, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithNoAmazonUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noNintendoCoUkLink()
    {
        $bindings = $this->getListBindings('No Nintendo.co.uk link', "[ 5, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithNoNintendoCoUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function brokenNintendoCoUkLink()
    {
        $bindings = $this->getListBindings('Broken Nintendo.co.uk link');

        $bindings['GameList'] = $this->getServiceGame()->getWithBrokenNintendoCoUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byCategory(Category $category)
    {
        $bindings = $this->getListBindings('By category: '.$category->name, "[ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getByCategory($category);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function bySeries(GameSeries $gameSeries)
    {
        $bindings = $this->getListBindings('By series: '.$gameSeries->series, "[ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getBySeries($gameSeries);

        $bindings['CustomHeader'] = 'Series';
        $bindings['ListMode'] = 'by-series';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byTag(Tag $tag)
    {
        $bindings = $this->getListBindings('By tag: '.$tag->tag_name, "[ 0, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getByTag($tag->id);

        return view('staff.games.list.standard-view', $bindings);
    }
}