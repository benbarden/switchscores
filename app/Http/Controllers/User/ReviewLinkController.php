<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class ReviewLinkController extends Controller
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceReviewSite = $serviceContainer->getReviewSiteService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();

        $bindings = [];

        $bindings['UserRegion'] = Auth::user()->region;

        $userId = Auth::id();

        $authUser = Auth::user();

        $siteId = $authUser->site_id;

        if (!$siteId) abort(403);

        $reviewSite = $serviceReviewSite->find($siteId);

        if (!$reviewSite) abort(403);

        $bindings['ReviewSite'] = $reviewSite;

        // Recent reviews
        $bindings['ReviewLinks'] = $serviceReviewLink->getAllBySite($siteId);
        $bindings['jsInitialSort'] = "[ 3, 'desc']";

        $bindings['TopTitle'] = 'Review links';
        $bindings['PageTitle'] = 'Review links';

        return view('user.review-link.list', $bindings);
    }
}
