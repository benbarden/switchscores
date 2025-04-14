<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\DbQueries as GameListsDb;
use App\Domain\ReviewLink\DbQueries as ReviewLinkDb;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use Illuminate\Routing\Controller as Controller;

class ListsController extends Controller
{
    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameListsDb $dbGameLists,
        private ReviewLinkDb $dbReviewLink,
        private Breadcrumbs $viewBreadcrumbs,
        private ReviewLinkRepository $repoReviewLink
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Lists');

        $bindings['TopTitle'] = 'Lists';
        $bindings['PageTitle'] = 'Lists';

        return view('public.lists.landing', $bindings);
    }

    public function recentReleases()
    {
        $bindings = [];

        $bindings['NewReleases'] = $this->repoGameLists->recentlyReleasedExceptLowQuality(50);
        $bindings['CalendarThisMonth'] = date('Y-m');

        $bindings['TopTitle'] = 'Nintendo Switch recent releases';
        $bindings['PageTitle'] = 'Nintendo Switch recent releases';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recent releases');

        return view('public.lists.list-recent-releases', $bindings);
    }

    public function upcomingReleases()
    {
        $bindings = [];

        $bindings['UpcomingNext7Days'] = $this->repoGameLists->upcomingNextDays(7);
        $bindings['UpcomingNext14Days'] = $this->repoGameLists->upcomingBetweenDays(7, 14);
        $bindings['UpcomingNext28Days'] = $this->repoGameLists->upcomingBetweenDays(14, 28);
        $bindings['UpcomingBeyond28Days'] = $this->repoGameLists->upcomingBeyondDays(28);

        $bindings['TopTitle'] = 'Nintendo Switch upcoming games';
        $bindings['PageTitle'] = 'Upcoming Nintendo Switch games';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Upcoming releases');

        return view('public.lists.list-upcoming-releases', $bindings);
    }

    public function gamesOnSale()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['PageTitle'] = 'Nintendo Switch games currently on sale in Europe';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Games on sale');

        $bindings['GoodRanks'] = $this->dbGameLists->onSaleGoodRanks(200);
        $bindings['HighestDiscounts'] = $this->dbGameLists->onSaleHighestDiscounts(200);
        $bindings['UnrankedDiscounts'] = $this->dbGameLists->onSaleUnranked(200);

        $bindings['TopRatedSort'] = "[8, 'desc'], [4, 'desc']";
        $bindings['HighestDiscountsSort'] = "[4, 'desc'], [8, 'desc']";
        $bindings['UnrankedDiscountsSort'] = "[6, 'desc'], [3, 'desc']";

        return view('public.lists.list-games-on-sale', $bindings);
    }

    public function recentReviews()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recent reviews');

        $bindings['TopTitle'] = 'Recent reviews of Nintendo Switch games';
        $bindings['PageTitle'] = 'Recent reviews';

        $bindings['ReviewList'] = $this->repoReviewLink->recentNaturalOrder(35);

        return view('public.lists.list-recent-reviews', $bindings);
    }

    public function recentlyRanked()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage('Recently ranked');

        $bindings['TopTitle'] = 'Recently ranked Nintendo Switch games';
        $bindings['PageTitle'] = 'Recently ranked';

        $highlightsRecentlyRanked = $this->dbReviewLink->recentlyRanked(28);

        $bindings['HighlightsRecentlyRanked'] = $highlightsRecentlyRanked;

        return view('public.lists.list-recently-ranked', $bindings);
    }

    public function buyersGuideHoliday2024US()
    {
        $pageTitle = 'Buyer\'s Guide Holiday 2024 (US)';
        $bindings = [];

        $topRatedAllTime = [
            1, // Zelda: Breath of the Wild
            66, // Super Mario Odyssey
            9416, // Metroid Prime Remastered
            74, // Celeste
            //4496, // Hades
            9452, // Zelda: Tears of the Kingdom
            8560, // Persona 5 Royal
            7740, // Xenoblade Chronicles 3
            //6696, // Tetris Effect: Connected
            4610, // Monster Hunter Rise
            40, // Mario Kart 8 Deluxe
            927, // Dead Cells
            //458, // SteamWorld Heist
            //4423, // A Short Hike
            67, // Hollow Knight
            11947, // Unicorn Overlord
            //8071, // Portal: Companion Collection
            98, // Stardew Valley
            627, // Super Smash Bros Ultimate
            2147, // Dragon Quest XI S
            7154, // Kirby and the Forgotten Land
            5893, // The Great Ace Attorney Chronicles
            2583, // Animal Crossing New Horizons
            11026, // Super Mario Bros Wonder
            12498, // Paper Mario: The Thousand Year Door
        ];

        $amazonBestsellers = [
            13592, // Mario Party Jamboree
            8104, // Pac-Man World Re-Pac
            7328, // Lego Star Wars Skywalker Saga
            12772, // Echoes of Wisdom
            40, // Mario Kart
            8981, // The Oregon Trail
            319, // Yoshi's Crafted World
            2126, // Super Mario Maker 2
            10614, // Sonic Superstars
            1223, // Super Mario Party
            627, // Super Smash Bros Ultimate
        ];

        $bindings['TopRatedAllTimeList'] = $this->repoGameLists->byIdList($topRatedAllTime);
        $bindings['AmazonBestsellersList'] = $this->repoGameLists->byIdList($amazonBestsellers);

        $bindings['crumbNav'] = $this->viewBreadcrumbs->listsSubpage($pageTitle);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('public.lists.buyers-guide-holiday-2024-us', $bindings);
    }

}
