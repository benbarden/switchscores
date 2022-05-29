<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\User\Repository as UserRepository;

use App\Models\QuickReview;
use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class IndexController extends Controller
{
    use SwitchServices;

    protected $repoFeaturedGames;
    protected $repoGameStats;
    protected $repoGameLists;
    protected $repoReviewDraft;
    protected $repoUser;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        GameListsRepository $repoGameLists,
        ReviewDraftRepository $repoReviewDraft,
        UserRepository $repoUser
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->repoGameLists = $repoGameLists;
        $this->repoReviewDraft = $repoReviewDraft;
        $this->repoUser = $repoUser;
    }

    public function index()
    {
        $pageTitle = 'Dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceQualityStats = new QualityStats();

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceQuickReview = $this->getServiceQuickReview();

        // Submissions
        $bindings['ReviewDraftUnprocessedCount'] = $this->repoReviewDraft->countUnprocessed();
        $unprocessedFeedReviewItems = $serviceReviewFeedItem->getUnprocessed();
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
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
        $bindings['PublisherMissingCount'] = $this->getServiceGamePublisher()->countGamesWithNoPublisher();
        $bindings['NoTagCount'] = $this->repoGameStats->totalUntagged();

        // New games
        $bindings['RecentlyAddedGames'] = $this->repoGameLists->recentlyAdded(10);

        // Owner links
        $bindings['RegisteredUserCount'] = $this->repoUser->getCount();

        // Data integrity
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        return view('staff.index', $bindings);
    }
}
