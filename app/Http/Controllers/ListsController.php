<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class ListsController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Lists');

        $bindings['TopTitle'] = 'Lists';
        $bindings['PageTitle'] = 'Lists';

        return view('lists.landing', $bindings);
    }

    public function recentReleases()
    {
        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleased(50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch recent releases';
        $bindings['PageTitle'] = 'Nintendo Switch recent releases';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recent releases');

        return view('lists.list-recent-releases', $bindings);
    }

    public function upcomingReleases()
    {
        $bindings = [];

        $bindings['UpcomingGames'] = $this->repoGameLists->upcoming();

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Upcoming releases');

        return view('lists.list-upcoming-releases', $bindings);
    }

    public function gamesOnSale()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Games on sale');

        $bindings['GoodRanks'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(200);
        $bindings['HighestDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleHighestDiscounts(200);
        $bindings['UnrankedDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleUnranked(200);

        $bindings['TopRatedSort'] = "[8, 'desc'], [4, 'desc']";
        $bindings['HighestDiscountsSort'] = "[4, 'desc'], [8, 'desc']";
        $bindings['UnrankedDiscountsSort'] = "[6, 'desc'], [3, 'desc']";

        return view('lists.list-games-on-sale', $bindings);
    }

    public function gamesOnSaleArchive()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Games on sale');

        $bindings['GoodRanks'] = $this->getServiceDataSourceParsed()->getGamesOnSaleGoodRanks(50);
        $bindings['HighestDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleHighestDiscounts(50);
        $bindings['UnrankedDiscounts'] = $this->getServiceDataSourceParsed()->getGamesOnSaleUnranked(50);

        //$bindings['AllGamesOnSale'] = $gamesOnSale;

        return view('lists.list-games-on-sale-archive', $bindings);
    }

    public function recentReviews()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recent reviews');

        $bindings['TopTitle'] = 'Recent reviews of Nintendo Switch games';
        $bindings['PageTitle'] = 'Recent reviews';

        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(35);

        return view('lists.list-recent-reviews', $bindings);
    }

    public function recentlyRanked()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recently ranked');

        $bindings['TopTitle'] = 'Recently ranked Nintendo Switch games';
        $bindings['PageTitle'] = 'Recently ranked';

        $highlightsRecentlyRanked = $this->getServiceReviewLink()->getHighlightsRecentlyRanked(28);

        foreach ($highlightsRecentlyRanked as &$item) {
            $item->ExtraDetailLine = 'Reviews: '.$item->review_count;
        }

        $bindings['HighlightsRecentlyRanked'] = $highlightsRecentlyRanked;

        return view('lists.list-recently-ranked', $bindings);
    }

    public function recentlyReviewedStillUnranked()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recently reviewed, still unranked');

        $bindings['TopTitle'] = 'Recently reviewed, still unranked Nintendo Switch games';
        $bindings['PageTitle'] = 'Recently reviewed, still unranked';

        $highlightsStillUnranked = $this->getServiceReviewLink()->getHighlightsStillUnranked(28);

        foreach ($highlightsStillUnranked as &$item) {
            $item->ExtraDetailLine = 'Reviews: '.$item->review_count;
        }

        $bindings['HighlightsStillUnranked'] = $highlightsStillUnranked;

        return view('lists.list-recently-reviewed-still-unranked', $bindings);
    }

}
