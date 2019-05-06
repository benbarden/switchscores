<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class IndexController extends Controller
{
    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceReviewSite = $serviceContainer->getReviewSiteService();

        $bindings = [];

        $bindings['TopTitle'] = 'Members dashboard';
        $bindings['PageTitle'] = 'Members dashboard';

        $bindings['UserRegion'] = Auth::user()->region;

        $userId = Auth::id();

        $siteId = Auth::user()->site_id;
        if ($siteId) {
            $reviewSite = $serviceReviewSite->find($siteId);
            if ($reviewSite) {
                $bindings['ReviewSite'] = $reviewSite;
            }
        }

        return view('user.index', $bindings);
    }
}
