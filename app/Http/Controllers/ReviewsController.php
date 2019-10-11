<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\WosServices;

use App\Services\ServiceContainer;

class ReviewsController extends Controller
{
    use WosServices;

    public function landing()
    {
        $bindings = [];

        $bindings['ReviewList'] = $this->getServiceReviewLink()->getLatestNaturalOrder(30);

        // Review counts
        $dateList = $this->getServiceGameCalendar()->getAllowedDates(false);
        $dateListArray = [];

        $dateListArray2017 = [];
        $dateListArray2018 = [];
        $dateListArray2019 = [];

        $reviewTotal2017 = 0;
        $reviewTotal2018 = 0;
        $reviewTotal2019 = 0;

        if ($dateList) {

            foreach ($dateList as $date) {

                list($dateYear, $dateMonth) = explode('-', $date);

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

                switch ($dateYear) {
                    case 2017:
                        $dateListArray2017[] = [
                            'DateRaw' => $date,
                            'ReviewCount' => $dateCount,
                        ];
                        $reviewTotal2017 += $dateCount;
                        break;
                    case 2018:
                        $dateListArray2018[] = [
                            'DateRaw' => $date,
                            'ReviewCount' => $dateCount,
                        ];
                        $reviewTotal2018 += $dateCount;
                        break;
                    case 2019:
                        $dateListArray2019[] = [
                            'DateRaw' => $date,
                            'ReviewCount' => $dateCount,
                        ];
                        $reviewTotal2019 += $dateCount;
                        break;
                }

            }

        }

        $bindings['DateList'] = $dateListArray;
        $bindings['DateList2017'] = $dateListArray2017;
        $bindings['DateList2018'] = $dateListArray2018;
        $bindings['DateList2019'] = $dateListArray2019;
        $bindings['ReviewTotal2017'] = $reviewTotal2017;
        $bindings['ReviewTotal2018'] = $reviewTotal2018;
        $bindings['ReviewTotal2019'] = $reviewTotal2019;

        $bindings['TopTitle'] = 'Nintendo Switch reviews and ratings';
        $bindings['PageTitle'] = 'Reviews';

        return view('reviews.landing', $bindings);
    }

    public function gamesNeedingReviews()
    {
        return redirect()->route('reviews.landing');
    }

    public function reviewSite($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $servicePartner = $serviceContainer->getPartnerService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();

        $reviewSite = $servicePartner->getByLinkTitle($linkTitle);

        if (!$reviewSite) {
            abort(404);
        }

        $siteId = $reviewSite->id;

        $bindings['TopTitle'] = $reviewSite->name.' - Site profile';
        $bindings['PageTitle'] = $reviewSite->name.' - Site profile';

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
