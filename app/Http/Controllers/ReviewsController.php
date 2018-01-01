<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\ReviewLinkService;

class ReviewsController extends BaseController
{
    public function landing()
    {
        $bindings = array();

        $serviceReviews = resolve('Services\ReviewLinkService');
        /* @var $serviceReviews \App\Services\ReviewLinkService */
        $reviewList = $serviceReviews->getLatestNaturalOrder(10);

        $bindings['ReviewList'] = $reviewList;
        $bindings['TopRatedNewReleases'] = $this->serviceGame->getListTopRatedLastXDays(30, 15);
        $bindings['TopRatedAllTime'] = $this->serviceGame->getListTopRated(10);

        $bindings['TopTitle'] = 'Nintendo Switch reviews and ratings';
        $bindings['PageTitle'] = 'Reviews';

        return view('reviews.landing', $bindings);
    }

    public function topRatedLanding()
    {
        $bindings = array();

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['TopRatedThisYear'] = $this->serviceGame->getListTopRatedByYear($thisYear, 15);
        $bindings['TopRatedNewReleases'] = $this->serviceGame->getListTopRatedLastXDays(30, 15);
        $bindings['TopRatedAllTime'] = $this->serviceGame->getListTopRated(15);

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('reviews.topRated.landing', $bindings);
    }

    public function topRatedAllTime()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListTopRated();

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('reviews.topRated.allTime', $bindings);
    }

    public function topRatedByYear($year)
    {
        $allowedYears = [2017, 2018];
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = array();

        $gamesList = $this->serviceGame->getListTopRatedByYear($year);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games - '.$year;
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games - '.$year;

        return view('reviews.topRated.byYear', $bindings);
    }

    public function gamesNeedingReviews()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListReviewsNeeded();

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[[6, 'desc'], [3, 'desc']]";

        $bindings['TopTitle'] = 'Nintendo Switch - Games needing more reviews';
        $bindings['PageTitle'] = 'Nintendo Switch games needing more reviews';

        return view('reviews.gamesNeedingReviews', $bindings);
    }

    public function reviewSite($linkTitle)
    {
        $bindings = array();

        $serviceReviewSite = resolve('Services\ReviewSiteService');
        $serviceReviewLink = resolve('Services\ReviewLinkService');
        /* @var $serviceReviewSite \App\Services\ReviewSiteService */
        /* @var $serviceReviewLink \App\Services\ReviewLinkService */

        $reviewSite = $serviceReviewSite->getByLinkTitle($linkTitle);

        if (!$reviewSite) {
            abort(404);
        }

        $siteId = $reviewSite->id;

        $bindings['TopTitle'] = $reviewSite->name.' - Site profile';
        $bindings['PageTitle'] = $reviewSite->name.' - Site profile';

        $bindings['ReviewSite'] = $reviewSite;

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
        $bindings['ReviewCount'] = $reviewStats[0]->ReviewCount;
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);
        $bindings['ReviewScoreDistribution'] = $reviewScoreDistribution;
        $bindings['MostUsedScore'] = $mostUsedScore;

        return view('reviews.site', $bindings);
    }

}
