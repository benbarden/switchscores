<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameStats\Repository as GameStatsRepositoryLegacy;
use App\Domain\Game\Repository\GameStatsRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Game\Repository\GameAffiliateRepository;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameStatsRepository $repoGameStats,
        private GameStatsRepositoryLegacy $repoGameStatsLegacy,
        private GameListsRepository $repoGameLists,
        private GameAffiliateRepository $repoGameAffiliate,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Games dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDashboard())->bindings;

        // Games to release
        $bindings['GamesForReleaseCount'] = $this->repoGameStatsLegacy->totalToBeReleased();

        // Missing data
        $bindings['NoNintendoCoUkLinkCount'] = $this->repoGameLists->noNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->repoGameLists->brokenNintendoCoUkLink()->count();
        $bindings['NoPriceCount'] = $this->repoGameStatsLegacy->totalNoPrice();
        $bindings['MissingVideoTypeCount'] = $this->repoGameStatsLegacy->totalNoVideoType();
        $bindings['MissingPlayersCount'] = DB::table('games')
            ->whereNull('players')
            ->where('game_status', 'active')
            ->count();

        // Game stats by status
        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ActiveGameCount'] = $this->repoGameStats->totalActive();
        $bindings['DelistedGameCount'] = $this->repoGameStats->totalDelisted();
        $bindings['SoftDeletedGameCount'] = $this->repoGameStats->totalSoftDeleted();

        // Verification
        $bindings['CategoryVerified'] = $this->repoGameStatsLegacy->totalCategoryVerified();
        $bindings['CategoryUnverified'] = $this->repoGameStatsLegacy->totalCategoryUnverified();
        $bindings['CategoryNeedsReview'] = $this->repoGameStatsLegacy->totalCategoryNeedsReview();
        $bindings['TagsVerified'] = $this->repoGameStatsLegacy->totalTagsVerified();
        $bindings['TagsUnverified'] = $this->repoGameStatsLegacy->totalTagsUnverified();
        $bindings['TagsNeedsReview'] = $this->repoGameStatsLegacy->totalTagsNeedsReview();

        // Affiliates
        $bindings['AmazonUSUncheckedCount'] = $this->repoGameAffiliate->countUnchecked('us');
        $bindings['AmazonUSLinkedCount'] = $this->repoGameAffiliate->countLinked('us');
        $bindings['AmazonUSNoProductCount'] = $this->repoGameAffiliate->countNoProduct('us');
        $bindings['AmazonUSIgnoredCount'] = $this->repoGameAffiliate->countIgnored('us');
        $bindings['AmazonUKUncheckedCount'] = $this->repoGameAffiliate->countUnchecked('uk');
        $bindings['AmazonUKLinkedCount'] = $this->repoGameAffiliate->countLinked('uk');
        $bindings['AmazonUKNoProductCount'] = $this->repoGameAffiliate->countNoProduct('uk');
        $bindings['AmazonUKIgnoredCount'] = $this->repoGameAffiliate->countIgnored('uk');

        // Crawl lifecycle stats (active games only)
        $bindings['CrawlNotYetCrawled'] = DB::table('games')
            ->whereNull('last_crawled_at')
            ->where('game_status', 'active')
            ->count();
        $bindings['CrawlStatus200'] = DB::table('games')
            ->where('last_crawl_status', 200)
            ->where('game_status', 'active')
            ->count();
        $bindings['CrawlStatus404'] = DB::table('games')
            ->where('last_crawl_status', 404)
            ->where('game_status', 'active')
            ->count();
        $bindings['CrawlStatus410'] = DB::table('games')
            ->where('last_crawl_status', 410)
            ->where('game_status', 'active')
            ->count();
        $bindings['CrawlStatusRedirect'] = DB::table('games')
            ->whereBetween('last_crawl_status', [300, 399])
            ->where('game_status', 'active')
            ->count();
        $bindings['CrawlStatusError'] = DB::table('games')
            ->where('last_crawl_status', '>=', 500)
            ->where('game_status', 'active')
            ->count();

        return view('staff.games.dashboard', $bindings);
    }

    public function stats()
    {
        $pageTitle = 'Games stats';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        // Game stats by status
        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ActiveGameCount'] = $this->repoGameStats->totalActive();
        $bindings['DelistedGameCount'] = $this->repoGameStats->totalDelisted();
        $bindings['SoftDeletedGameCount'] = $this->repoGameStats->totalSoftDeleted();

        // Format stats
        $bindings['FormatDigital'] = $this->repoGameStats->getFormatDigital();
        $bindings['FormatPhysical'] = $this->repoGameStats->getFormatPhysical();
        $bindings['FormatDLC'] = $this->repoGameStats->getFormatDLC();
        $bindings['FormatDemo'] = $this->repoGameStats->getFormatDemo();

        return view('staff.games.stats', $bindings);
    }
}
