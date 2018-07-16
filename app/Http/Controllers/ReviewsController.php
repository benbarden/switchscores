<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Services\ReviewLinkService;
use App\Services\ReviewSiteService;
use App\Services\TopRatedService;
use App\Services\GameReleaseDateService;

class ReviewsController extends BaseController
{
    public function landing()
    {
        $regionCode = \Request::get('regionCode');

        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated \App\Services\TopRatedService */
        $serviceReviewLinks = resolve('Services\ReviewLinkService');
        /* @var $serviceReviewLinks \App\Services\ReviewLinkService */
        $serviceReviewSites = resolve('Services\ReviewSiteService');
        /* @var $serviceReviewSites \App\Services\ReviewSiteService */

        $bindings = [];

        $reviewList = $serviceReviewLinks->getLatestNaturalOrder(10);
        $reviewPartnerList = $serviceReviewSites->getActive();

        $bindings['ReviewPartnerList'] = $reviewPartnerList;
        $bindings['ReviewList'] = $reviewList;
        $bindings['TopRatedNewReleases'] = $serviceTopRated->getLastXDays($regionCode, 30, 15);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 10);

        $bindings['TopTitle'] = 'Nintendo Switch reviews and ratings';
        $bindings['PageTitle'] = 'Reviews';

        return view('reviews.landing', $bindings);
    }

    public function topRatedLanding()
    {
        $regionCode = \Request::get('regionCode');

        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated \App\Services\TopRatedService */

        $bindings = [];

        $thisYear = date('Y');
        $bindings['Year'] = $thisYear;
        $bindings['TopRatedThisYear'] = $serviceTopRated->getByYear($regionCode, $thisYear, 15);
        $bindings['TopRatedNewReleases'] = $serviceTopRated->getLastXDays($regionCode, 30, 15);
        $bindings['TopRatedAllTime'] = $serviceTopRated->getList($regionCode, 15);

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('reviews.topRated.landing', $bindings);
    }

    public function topRatedAllTime()
    {
        $regionCode = \Request::get('regionCode');

        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated \App\Services\TopRatedService */

        $bindings = [];

        $gamesList = $serviceTopRated->getList($regionCode);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('reviews.topRated.allTime', $bindings);
    }

    public function topRatedByYear($year)
    {
        $regionCode = \Request::get('regionCode');

        $serviceTopRated = resolve('Services\TopRatedService');
        /* @var $serviceTopRated \App\Services\TopRatedService */

        $allowedYears = [2017, 2018];
        if (!in_array($year, $allowedYears)) {
            abort(404);
        }

        $bindings = [];

        $gamesList = $serviceTopRated->getByYear($regionCode, $year);

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";
        $bindings['Year'] = $year;

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games - '.$year;
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games - '.$year;

        return view('reviews.topRated.byYear', $bindings);
    }

    public function gamesNeedingReviews()
    {
        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = resolve('Services\GameReleaseDateService');
        /* @var $serviceGameReleaseDate GameReleaseDateService */

        $bindings = [];

        $gamesList = $serviceGameReleaseDate->getReviewsNeeded($regionCode);

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
        /* @var $serviceReviewSite \App\Services\ReviewSiteService */
        $serviceReviewLink = resolve('Services\ReviewLinkService');
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
