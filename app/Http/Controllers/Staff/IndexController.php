<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\User\Repository as UserRepository;
use App\Domain\GamePublisher\DbQueries as GamePublisherDbQueries;

use App\Models\QuickReview;
use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class IndexController extends Controller
{
    use SwitchServices;

    private $repoFeaturedGames;
    private $repoGameStats;
    private $repoGameLists;
    private $repoReviewDraft;
    private $repoUser;
    private $dbGamePublisher;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        GameListsRepository $repoGameLists,
        ReviewDraftRepository $repoReviewDraft,
        UserRepository $repoUser,
        GamePublisherDbQueries $dbGamePublisher
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->repoGameLists = $repoGameLists;
        $this->repoReviewDraft = $repoReviewDraft;
        $this->repoUser = $repoUser;
        $this->dbGamePublisher = $dbGamePublisher;
    }

    public function index()
    {
        $pageTitle = 'Dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();

        $serviceQuickReview = $this->getServiceQuickReview();

        // Submissions
        $bindings['ReviewDraftUnprocessedCount'] = $this->repoReviewDraft->countUnprocessed();
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);
        $bindings['PendingFeaturedGameCount'] = $this->repoFeaturedGames->countPending();

        // Games to add
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();
        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();

        // Missing data
        $bindings['NoCategoryCount'] = $this->repoGameStats->totalNoCategory();
        $bindings['NoTagCount'] = $this->repoGameStats->totalUntagged();
        $bindings['PublisherMissingCount'] = $this->dbGamePublisher->countGamesWithNoPublisher();
        $bindings['NoNintendoCoUkLinkCount'] = $this->getServiceGame()->getWithNoNintendoCoUkLink()->count();
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        // New games
        $bindings['RecentlyReleasedGames'] = $this->getServiceGame()->getRecentlyReleased(15);
        $bindings['RecentlyAddedGames'] = $this->repoGameLists->recentlyAdded(15);

        // Owner links
        $bindings['RegisteredUserCount'] = $this->repoUser->getCount();

        return view('staff.index', $bindings);
    }
}
