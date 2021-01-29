<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ReviewSiteController extends Controller
{
    use SwitchServices;
    use StaffView;

    protected $repoFeaturedGames;
    protected $repoGameStats;

    public function __construct(
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats
    )
    {
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
    }

    public function show()
    {
        $bindings = $this->getBindingsStatsSubpage('Review site stats');

        $serviceReviewLinks = $this->getServiceReviewLink();
        $servicePartner = $this->getServicePartner();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceGame = $this->getServiceGame();
        $serviceTopRated = $this->getServiceTopRated();
        $serviceReviewStats = $this->getServiceReviewStats();

        $bindings['RankedGameCount'] = $this->repoGameStats->totalRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount();

        $releasedGameCount = $this->repoGameStats->totalReleased();
        $reviewLinkCount = $serviceReviewLinks->countActive();

        $bindings['ReleasedGameCount'] = $releasedGameCount;
        $bindings['ReviewLinkCount'] = $reviewLinkCount;

        $reviewSitesActive = $servicePartner->getActiveReviewSites();
        $reviewSitesRender = [];

        foreach ($reviewSitesActive as $reviewSite) {

            $id = $reviewSite->id;
            $name = $reviewSite->name;
            $linkTitle = $reviewSite->link_title;
            $reviewCount = $reviewSite->review_count;
            $latestReviewDate = $reviewSite->last_review_date;

            $reviewLinkContribTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $reviewLinkCount);
            $reviewGameCompletionTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $releasedGameCount);

            $reviewSitesRender[] = [
                'id' => $id,
                'name' => $name,
                'link_title' => $linkTitle,
                'review_count' => $reviewCount,
                'review_link_contrib_total' => $reviewLinkContribTotal,
                'review_game_completion_total' => $reviewGameCompletionTotal,
                'latest_review_date' => $latestReviewDate,
            ];

        }

        $bindings['ReviewSitesArray'] = $reviewSitesRender;

        return view('staff.stats.reviewSites', $bindings);
    }
}
