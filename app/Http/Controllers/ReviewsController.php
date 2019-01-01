<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class ReviewsController extends BaseController
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceReviewSites = $serviceContainer->getReviewSiteService();

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

    public function gamesNeedingReviews()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $serviceReviewSite = $serviceContainer->getReviewSiteService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();

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
