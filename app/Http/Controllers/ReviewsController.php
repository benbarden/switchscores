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

    public function topRatedAllTime()
    {
        $bindings = array();

        $gamesList = $this->serviceGame->getListTopRated();

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = "[5, 'desc']";

        $bindings['TopTitle'] = 'Nintendo Switch Top Rated games';
        $bindings['PageTitle'] = 'Top Rated Nintendo Switch games';

        return view('reviews.topRatedAllTime', $bindings);
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

}
