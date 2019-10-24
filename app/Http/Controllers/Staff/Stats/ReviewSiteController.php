<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use Auth;

class ReviewSiteController extends Controller
{
    use SiteRequestData;
    use WosServices;

    public function show()
    {
        $regionCode = $this->getRegionCode();

        $bindings = [];

        $serviceReviewLinks = $this->getServiceReviewLink();
        $servicePartner = $this->getServicePartner();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceGameRankAllTime = $this->getServiceGameRankAllTime();
        $serviceTopRated = $this->getServiceTopRated();
        $serviceReviewStats = $this->getServiceReviewStats();

        $bindings['RankedGameCount'] = $serviceGameRankAllTime->countRanked();
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount($regionCode);

        $releasedGameCount = $serviceGameReleaseDate->countReleased($regionCode);
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

        $bindings['PageTitle'] = 'Review site stats';
        $bindings['TopTitle'] = 'Staff - Stats - Review sites';

        return view('staff.stats.reviewSites', $bindings);
    }
}
