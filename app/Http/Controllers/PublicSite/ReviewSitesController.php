<?php

namespace App\Http\Controllers\PublicSite;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewLink\Stats as ReviewLinkStatsRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class ReviewSitesController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private ReviewLinkRepository $repoReviewLink,
        private ReviewLinkStatsRepository $repoReviewLinkStats,
        private ReviewSiteRepository $repoReviewSite,
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Review sites';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        $bindings['ReviewPartnerList'] = $this->repoReviewSite->getActive();

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
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::partnersSubpage($pageTitle))->bindings;

        $siteId = $reviewSite->id;

        $bindings['PartnerData'] = $reviewSite;

        $siteReviewsLatest = $this->repoReviewLink->bySiteLatest($siteId);
        $reviewScoreDistribution = $this->repoReviewLinkStats->scoreDistributionBySite($siteId);

        $mostUsedScore = ['topScore' => 0, 'topScoreCount' => 0];
        if ($reviewScoreDistribution) {
            foreach ($reviewScoreDistribution as $scoreKey => $scoreVal) {
                if ($scoreVal > $mostUsedScore['topScoreCount']) {
                    $mostUsedScore = ['topScore' => $scoreKey, 'topScoreCount' => $scoreVal];
                }
            }
        }

        $bindings['SiteReviewsLatest'] = $siteReviewsLatest;
        $bindings['ReviewScoreDistribution'] = $reviewScoreDistribution;
        $bindings['MostUsedScore'] = $mostUsedScore;

        return view('public.partners.review-sites.profile', $bindings);
    }
}
