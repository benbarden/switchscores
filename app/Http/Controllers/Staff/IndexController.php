<?php

namespace App\Http\Controllers\Staff;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Models\QuickReview;
use App\Services\DataQuality\QualityStats;
use App\Services\Migrations\Category as MigrationsCategory;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class IndexController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $viewBreadcrumbs;
    protected $repoFeaturedGames;
    protected $repoGameStats;
    protected $repoGameLists;
    protected $repoReviewDraft;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        GameListsRepository $repoGameLists,
        ReviewDraftRepository $repoReviewDraft
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->repoGameLists = $repoGameLists;
        $this->repoReviewDraft = $repoReviewDraft;
    }

    public function index()
    {
        $bindings = $this->getBindings('Staff index');

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Dashboard');

        $serviceQualityStats = new QualityStats();
        $serviceMigrationsCategory = new MigrationsCategory();
        $serviceUser = $this->getServiceUser();

        $serviceReviewFeedItem = $this->getServiceReviewFeedItem();
        $serviceQuickReview = $this->getServiceQuickReview();

        // Submissions
        $bindings['ReviewDraftUnprocessedCount'] = $this->repoReviewDraft->countUnprocessed();
        $unprocessedFeedReviewItems = $serviceReviewFeedItem->getUnprocessed();
        $bindings['UnprocessedFeedReviewItemsCount'] = count($unprocessedFeedReviewItems);
        $pendingQuickReview = $serviceQuickReview->getByStatus(QuickReview::STATUS_PENDING);
        $bindings['PendingQuickReviewCount'] = count($pendingQuickReview);
        $pendingCategoryEdits = $this->getServiceDbEditGame()->getPendingCategoryEdits();
        $bindings['PendingGameCategorySuggestionCount'] = count($pendingCategoryEdits);
        $bindings['PendingFeaturedGameCount'] = $this->repoFeaturedGames->countPending();

        // Games to add
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();
        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $unlinkedItemList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['NintendoCoUkUnlinkedCount'] = $unlinkedItemList->count();

        // Missing data
        $bindings['NoCategoryAllGamesCount'] = $serviceMigrationsCategory->countGamesWithNoCategory();
        $bindings['PublisherMissingCount'] = $this->getServiceGamePublisher()->countGamesWithNoPublisher();
        $bindings['NoTagCount'] = $this->repoGameStats->totalUntagged();

        // New games
        $bindings['RecentlyAddedGames'] = $this->repoGameLists->recentlyAdded(10);

        // Owner links
        $bindings['RegisteredUserCount'] = $serviceUser->getCount();

        // Data integrity
        $bindings['DuplicateReviewsCount'] = count($serviceQualityStats->getDuplicateReviews());

        // Recent imports
        $bindings['ReviewFeedImportList'] = $this->getServiceReviewFeedImport()->getLive(5);

        return view('staff.index', $bindings);
    }
}
