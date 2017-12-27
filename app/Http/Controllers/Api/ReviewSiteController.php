<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;

class ReviewSiteController extends BaseController
{
    public function getByDomain()
    {
        $request = request();

        $reviewUrl = $request->reviewUrl;

        if (!$reviewUrl) {
            return response()->json(['error' => 'Missing data: reviewUrl'], 404);
        }

        $serviceReviewSite = resolve('Services\ReviewSiteService');
        /* @var \App\Services\ReviewSiteService $serviceReviewSite */

        // Convert to domain URL
        $domainUrl = $reviewUrl;
        $domainUrl = str_replace('http://', '', $domainUrl);
        $domainUrl = str_replace('https://', '', $domainUrl);
        $domainUrlArray = explode('/', $domainUrl);
        $domainUrl = $domainUrlArray[0].'/';

        $reviewSite = $serviceReviewSite->getByDomain($domainUrl);

        if ($reviewSite) {
            $data = array(
                'siteId' => $reviewSite->id,
                'siteName' => $reviewSite->name
            );
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => 'Domain not found: '.$domainUrl], 404);
        }
    }
}