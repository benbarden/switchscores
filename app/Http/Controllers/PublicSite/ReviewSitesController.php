<?php

namespace App\Http\Controllers\PublicSite;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

use Illuminate\Routing\Controller as Controller;

class ReviewSitesController extends Controller
{
    use SwitchServices;

    public function __construct(
        private Breadcrumbs $viewBreadcrumbs,
        private ReviewLinkRepository $repoReviewLink,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Review sites';

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($pageTitle);

        $bindings['ReviewPartnerList'] = $this->repoReviewSite->getActive();

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('public.partners.review-sites.landing', $bindings);
    }

    public function siteProfile($linkTitle)
    {
        $bindings = [];

        $reviewSite = $this->repoReviewSite->getByLinkTitle($linkTitle);

        if (!$reviewSite) {
            abort(404);
        }

        $pageTitle = $reviewSite->name.' - Profile';

        $siteId = $reviewSite->id;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage($pageTitle);

        $bindings['PartnerData'] = $reviewSite;

        $siteReviewsLatest = $this->repoReviewLink->bySiteLatest($siteId);
        $reviewStats = $this->getServiceReviewLink()->getSiteReviewStats($siteId);
        $reviewScoreDistribution = $this->getServiceReviewLink()->getSiteScoreDistribution($siteId);

        $mostUsedScore = ['topScore' => 0, 'topScoreCount' => 0];
        if ($reviewScoreDistribution) {
            foreach ($reviewScoreDistribution as $scoreKey => $scoreVal) {
                if ($scoreVal > $mostUsedScore['topScoreCount']) {
                    $mostUsedScore = ['topScore' => $scoreKey, 'topScoreCount' => $scoreVal];
                }
            }
        }

        $bindings['SiteReviewsLatest'] = $siteReviewsLatest;
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);
        $bindings['ReviewScoreDistribution'] = $reviewScoreDistribution;
        $bindings['MostUsedScore'] = $mostUsedScore;

        return view('public.partners.review-sites.siteProfile', $bindings);
    }
}
