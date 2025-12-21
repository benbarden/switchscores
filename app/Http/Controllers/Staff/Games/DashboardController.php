<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameStats\Repository as GameStatsRepositoryLegacy;
use App\Domain\Game\Repository\GameStatsRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Game\Repository\GameAffiliateRepository;

class DashboardController extends Controller
{
    public function __construct(
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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Games to release
        $bindings['GamesForReleaseCount'] = $this->repoGameStatsLegacy->totalToBeReleased();

        // Missing data
        $bindings['NoNintendoCoUkLinkCount'] = $this->repoGameLists->noNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->repoGameLists->brokenNintendoCoUkLink()->count();
        $bindings['NoPriceCount'] = $this->repoGameStatsLegacy->totalNoPrice();
        $bindings['MissingVideoTypeCount'] = $this->repoGameStatsLegacy->totalNoVideoType();

        // Release date stats
        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $this->repoGameStats->totalUpcoming();

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

        return view('staff.games.dashboard', $bindings);
    }

    public function stats()
    {
        $pageTitle = 'Games stats';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Release date stats
        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $this->repoGameStats->totalUpcoming();

        // Format stats
        $bindings['FormatDigital'] = $this->repoGameStats->getFormatDigital();
        $bindings['FormatPhysical'] = $this->repoGameStats->getFormatPhysical();
        $bindings['FormatDLC'] = $this->repoGameStats->getFormatDLC();
        $bindings['FormatDemo'] = $this->repoGameStats->getFormatDemo();

        return view('staff.games.stats', $bindings);
    }
}
