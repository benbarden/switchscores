<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

class DashboardController extends Controller
{
    public function __construct(
        private GameStatsRepository $repoGameStats,
        private GameListsRepository $repoGameLists
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Games dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Games to release
        $bindings['GamesForReleaseCount'] = $this->repoGameStats->totalToBeReleased();

        // Missing data
        $bindings['NoNintendoCoUkLinkCount'] = $this->repoGameLists->noNintendoCoUkLink()->count();
        $bindings['BrokenNintendoCoUkLinkCount'] = $this->repoGameLists->brokenNintendoCoUkLink()->count();
        $bindings['NoPriceCount'] = $this->repoGameStats->totalNoPrice();
        $bindings['MissingVideoTypeCount'] = $this->repoGameStats->totalNoVideoType();
        $bindings['MissingAmazonUkLink'] = $this->repoGameStats->totalNoAmazonUkLink();
        $bindings['MissingAmazonUsLink'] = $this->repoGameStats->totalNoAmazonUsLink();

        // Release date stats
        $bindings['TotalGameCount'] = $this->repoGameStats->grandTotal();
        $bindings['ReleasedGameCount'] = $this->repoGameStats->totalReleased();
        $bindings['UpcomingGameCount'] = $this->repoGameStats->totalUpcoming();

        // Verification
        $bindings['CategoryVerified'] = $this->repoGameStats->totalCategoryVerified();
        $bindings['CategoryUnverified'] = $this->repoGameStats->totalCategoryUnverified();
        $bindings['CategoryNeedsReview'] = $this->repoGameStats->totalCategoryNeedsReview();
        $bindings['TagsVerified'] = $this->repoGameStats->totalTagsVerified();
        $bindings['TagsUnverified'] = $this->repoGameStats->totalTagsUnverified();
        $bindings['TagsNeedsReview'] = $this->repoGameStats->totalTagsNeedsReview();

        // Format stats
        $bindings['FormatDigital'] = $this->repoGameStats->getFormatDigital();
        $bindings['FormatPhysical'] = $this->repoGameStats->getFormatPhysical();
        $bindings['FormatDLC'] = $this->repoGameStats->getFormatDLC();
        $bindings['FormatDemo'] = $this->repoGameStats->getFormatDemo();

        return view('staff.games.dashboard', $bindings);
    }
}
