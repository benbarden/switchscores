<?php

namespace App\Http\Controllers\Staff\Games;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Models\Category;
use App\Models\GameSeries;
use App\Models\Tag;
use App\Services\Migrations\Category as MigrationsCategory;
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
        $bindings = $this->getBindingsGamesSubpage('Games to release', "[ 6, 'asc'], [ 1, 'asc']");

        $bindings['GameList'] = $this->repoGameLists->gamesForRelease();

        $bindings['CustomHeader'] = 'Action';
        $bindings['ListMode'] = 'games-to-release';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyAdded()
    {
        $bindings = $this->getBindingsGamesSubpage('Recently added');

        $bindings['GameList'] = $this->repoGameLists->recentlyAdded(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function recentlyReleased()
    {
        $bindings = $this->getBindingsGamesSubpage('Recently released', "[ 6, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getRecentlyReleased(100);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingGames()
    {
        $bindings = $this->getBindingsGamesSubpage('Upcoming and unreleased', "[ 6, 'asc']");

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllUnreleased();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function upcomingEshopCrosscheck()
    {
        $bindings = $this->getBindingsGamesSubpage('Upcoming (eShop crosscheck)', "[ 6, 'asc']");

        $bindings['GameList'] = $this->repoGameLists->upcomingEshopCrosscheck();
        $bindings['GameListNoDate'] = $this->repoGameLists->upcomingEshopCrosscheckNoDate();

        return view('staff.games.list.upcoming-eshop-crosscheck', $bindings);
    }

    public function noCategory()
    {
        $bindings = $this->getBindingsGamesSubpage('No category', "[ 0, 'desc']");

        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithNoCategory();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noTag()
    {
        $bindings = $this->getBindingsGamesSubpage('No tag', "[ 0, 'desc']");

        $bindings['GameList'] = $this->repoGameLists->noTag();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEuReleaseDate()
    {
        $bindings = $this->getBindingsGamesSubpage('No EU release date', "[ 0, 'desc']");

        $bindings['GameList'] = $this->getServiceGameReleaseDate()->getAllWithNoEuReleaseDate();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noEshopPrice()
    {
        $bindings = $this->getBindingsGamesSubpage('No eShop price', "[ 6, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithoutPrices();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noVideoType()
    {
        $bindings = $this->getBindingsGamesSubpage('No video type', "[ 0, 'asc']");

        $bindings['GameList'] = $this->repoGameLists->noVideoType();
        $bindings['ListLimit'] = "200";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noAmazonUkLink()
    {
        $bindings = $this->getBindingsGamesSubpage('No Amazon UK link', "[ 0, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithNoAmazonUkLink();
        $bindings['ListLimit'] = "200";

        return view('staff.games.list.standard-view', $bindings);
    }

    public function noNintendoCoUkLink()
    {
        $bindings = $this->getBindingsGamesSubpage('No Nintendo.co.uk link', "[ 6, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getWithNoNintendoCoUkLink();

        return view('staff.games.list.standard-view', $bindings);
    }

    public function brokenNintendoCoUkLink()
    {
        $bindings = $this->getBindingsGamesSubpage('Broken Nintendo.co.uk link');

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
        $bindings = $this->getBindingsGamesSubpage('By format option: '.$format.' - '.$valueDesc);

        $bindings['GameList'] = $this->repoGameLists->formatOption($format, $value);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byCategory(Category $category)
    {
        $bindings = $this->getBindingsGamesSubpage('By category: '.$category->name, "[ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getByCategory($category);

        return view('staff.games.list.standard-view', $bindings);
    }

    public function bySeries(GameSeries $gameSeries)
    {
        $bindings = $this->getBindingsGamesSubpage('By series: '.$gameSeries->series, "[ 1, 'asc']");

        $bindings['GameList'] = $this->getServiceGame()->getBySeries($gameSeries);

        $bindings['CustomHeader'] = 'Series';
        $bindings['ListMode'] = 'by-series';

        return view('staff.games.list.standard-view', $bindings);
    }

    public function byTag(Tag $tag)
    {
        $bindings = $this->getBindingsGamesSubpage('By tag: '.$tag->tag_name, "[ 0, 'desc']");

        $bindings['GameList'] = $this->getServiceGame()->getByTag($tag->id);

        return view('staff.games.list.standard-view', $bindings);
    }
}