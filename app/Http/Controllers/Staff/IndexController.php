<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;
use App\Domain\ViewBindings\Staff as Bindings;

use App\Models\QuickReview;
use App\Services\DataQuality\QualityStats;
use App\Services\Migrations\Category as MigrationsCategory;

use App\Traits\SwitchServices;

class IndexController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;
    protected $viewBindings;
    protected $repoFeaturedGames;
    protected $repoGameStats;
    protected $repoGameLists;
    protected $repoReviewDraft;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        Bindings $viewBindings,
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats,
        GameListsRepository $repoGameLists,
        ReviewDraftRepository $repoReviewDraft
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->viewBindings = $viewBindings;
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
        $this->repoGameLists = $repoGameLists;
        $this->repoReviewDraft = $repoReviewDraft;
    }

    public function index()
    {
        $breadcrumbs = $this->viewBreadcrumbs->topLevelPage('Dashboard');

        $bindings = $this->viewBindings->setBreadcrumbs($breadcrumbs)->generateStaff('Staff index');

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
