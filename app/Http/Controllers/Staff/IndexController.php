<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\User\Repository as UserRepository;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;
use App\Domain\GamesCompanySignup\Repository as GamesCompanyRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

use App\Models\QuickReview;
use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class IndexController extends Controller
{
    use SwitchServices;

    public function __construct(
        private FeaturedGameRepository $repoFeaturedGames,
        private GameStatsRepository $repoGameStats,
        private GameListsRepository $repoGameLists,
        private ReviewDraftRepository $repoReviewDraft,
        private QuickReviewRepository $repoQuickReview,
        private UserRepository $repoUser,
        private GamePublisherDbQueries $dbGamePublisher,
        private GamesCompanyRepository $repoGamesCompany,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();

        // Submissions
        $bindings['ReviewDraftUnprocessedCount'] = $this->repoReviewDraft->countUnprocessed();
        $pendingQuickReview = $this->repoQuickReview->byStatus(QuickReview::STATUS_PENDING);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);
        $bindings['PendingFeaturedGameCount'] = $this->repoFeaturedGames->countPending();
        $bindings['TotalGamesCompanySignups'] = $this->repoGamesCompany->countTotal();

        // Games to add
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();
        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->repoDataSourceParsed->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();

        // Nintendo links
        $bindings['NoNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithNoNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithBrokenNintendoCoUkLink()->count();

        // Missing data
        $bindings['NoCategoryExcludingLowQualityCount'] = $this->repoGameStats->totalNoCategoryExcludingLowQuality();
        $bindings['NoCategoryAllCount'] = $this->repoGameStats->totalNoCategoryAll();
        $bindings['NoCategoryWithCollectionCount'] = $this->repoGameStats->totalNoCategoryWithCollectionId();
        $bindings['PublisherMissingCount'] = $this->dbGamePublisher->countGamesWithNoPublisher();
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        // New games
        $bindings['RecentlyReleasedGames'] = $this->repoGameLists->recentlyReleasedAll(15);
        $bindings['RecentlyAddedGames'] = $this->repoGameLists->recentlyAdded(15);

        // Owner links
        $bindings['RegisteredUserCount'] = $this->repoUser->getCount();

        return view('staff.index', $bindings);
    }
}
