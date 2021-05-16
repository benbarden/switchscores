<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class ReviewsController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Reviews');

        $bindings['TopTitle'] = 'Nintendo Switch reviews and ratings';
        $bindings['PageTitle'] = 'Reviews';

        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(35);
        $highlightsRecentlyRanked = $this->getServiceReviewLink()->getHighlightsRecentlyRanked(14);
        $highlightsStillUnranked = $this->getServiceReviewLink()->getHighlightsStillUnranked(14);

        foreach ($highlightsRecentlyRanked as &$item) {
            $item->ExtraDetailLine = 'Reviews: '.$item->review_count;
        }
        foreach ($highlightsStillUnranked as &$item) {
            $item->ExtraDetailLine = 'Reviews: '.$item->review_count;
        }

        $bindings['HighlightsRecentlyRanked'] = $highlightsRecentlyRanked;
        $bindings['HighlightsStillUnranked'] = $highlightsStillUnranked;

        return view('reviews.landing', $bindings);
    }

    public function landingByYear($year)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Review stats - '.$year;
        $bindings['PageTitle'] = 'Review stats - '.$year;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage('Review stats - '.$year);

        $bindings['ReviewYear'] = $year;

        // Review counts
        $dateList = $this->getServiceGameCalendar()->getAllowedDates(false);
        $dateListArray = [];
        $reviewTotal = 0;

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

                if ($dateYear != $year) continue;

                $reviewLinkStat = $this->getServiceReviewLink()->countActiveByYearMonth($dateYear, $dateMonth);
                if ($reviewLinkStat) {
                    $dateCount = $reviewLinkStat;
                } else {
                    $dateCount = 0;
                }

                if ($dateCount == 0) continue;

                $dateListArray[] = [
                    'DateRaw' => $date,
                    'ReviewCount' => $dateCount,
                ];
                $reviewTotal += $dateCount;

            }

        }

        // Score distribution
        $bindings['ScoreDistributionByYear'] = $this->getServiceReviewLink()->getFullScoreDistributionByYear($year);

        // Ranked/Unranked count
        $bindings['RankedCountByYear'] = $this->getServiceTopRated()->getRankedCountByYear($year);

        // Review count stats
        $bindings['ReviewCountStatsByYear'] = $this->getServiceReviewLink()->getReviewCountStatsByYear($year);

        $bindings['DateList'] = $dateListArray;
        $bindings['ReviewTotal'.$year] = $reviewTotal;

        return view('reviews.landingByYear', $bindings);
    }

    public function reviewSite($linkTitle)
    {
        $bindings = [];

        $servicePartner = $this->getServicePartner();
        $serviceReviewLink = $this->getServiceReviewLink();

        $reviewSite = $servicePartner->getByLinkTitle($linkTitle);

        if (!$reviewSite) {
            abort(404);
        }

        $siteId = $reviewSite->id;

        $bindings['TopTitle'] = $reviewSite->name.' - Site profile';
        $bindings['PageTitle'] = $reviewSite->name.' - Site profile';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage($reviewSite->name.' - Site profile');

        $bindings['PartnerData'] = $reviewSite;

        $siteReviewsLatest = $serviceReviewLink->getLatestBySite($siteId);
        $reviewStats = $serviceReviewLink->getSiteReviewStats($siteId);
        $reviewScoreDistribution = $serviceReviewLink->getSiteScoreDistribution($siteId);

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

        return view('reviews.site', $bindings);
    }

}
