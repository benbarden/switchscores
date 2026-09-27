<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\User\Repository as UserRepository;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GamesCompanySignup\Repository as GamesCompanyRepository;
use App\Domain\Contact\Repository as ContactRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\StaffDashboard\AttentionTiles;

use App\Models\QuickReview;

class IndexController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private FeaturedGameRepository $repoFeaturedGames,
        private GameStatsRepository $repoGameStats,
        private GameListsRepository $repoGameLists,
        private ReviewDraftRepository $repoReviewDraft,
        private QuickReviewRepository $repoQuickReview,
        private UserRepository $repoUser,
        private GamePublisherDbQueries $dbGamePublisher,
        private GamesCompanyRepository $repoGamesCompany,
        private ContactRepository $repoContact,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private AttentionTiles $attentionTiles,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::staffDashboard())->bindings;

        // Needs attention (#163): queue counts feed the tiles, which add age and state
        $pendingQuickReview = $this->repoQuickReview->byStatus(QuickReview::STATUS_PENDING);
        $bindings['AttentionTiles'] = $this->attentionTiles->build(auth()->user(), [
            'reviewDrafts' => $this->repoReviewDraft->countUnprocessed(),
            'quickReviews' => count($pendingQuickReview),
            'featuredGames' => $this->repoFeaturedGames->countPending(),
            'contact' => $this->repoContact->countNewSubmissions(),
            'signups' => $this->repoGamesCompany->countTotal(),
        ]);

        // Games to add
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();

        // Nintendo links
        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->repoDataSourceParsed->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();
        $bindings['NoNintendoCoUkLinkCount'] = $this->repoGameLists->noNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->repoGameLists->brokenNintendoCoUkLink()->count();

        // Missing data
        $bindings['NoCategoryExcludingLowQualityCount'] = $this->repoGameStats->totalNoCategoryExcludingLowQuality();
        $bindings['NoCategoryAllCount'] = $this->repoGameStats->totalNoCategoryAll();
        $bindings['PublisherMissingCount'] = $this->dbGamePublisher->countGamesWithNoPublisher();

        // Visual action lists - condensed to 5 each (#163, decision 2)
        $bindings['CrawlPriorityQueue'] = $this->repoGameLists->crawlPriorityQueue(5);
        if ($bindings['CrawlPriorityQueue']->isEmpty()) {
            $bindings['NextToCrawl'] = $this->repoGameLists->nextToCrawl(5);
        }

        // New games
        $bindings['RecentlyAddedGames'] = $this->repoGameLists->recentlyAdded(5);

        // Owner links
        $bindings['RegisteredUserCount'] = $this->repoUser->getCount();
        $bindings['UnverifiedMemberCount'] = $this->repoUser->getUnverifiedCount();

        return view('staff.index', $bindings);
    }
}
