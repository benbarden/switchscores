<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepo;

class ReviewSiteController extends Controller
{
    public function __construct(
        private ReviewSiteRepo $repoReviewSite
    )
    {
    }

    public function getByDomain()
    {
        $request = request();

        $reviewUrl = $request->reviewUrl;

        if (!$reviewUrl) {
            return response()->json(['error' => 'Missing data: reviewUrl'], 404);
        }

        // Convert to domain URL
        $domainUrl = $reviewUrl;
        $domainUrl = str_replace('http://', '', $domainUrl);
        $domainUrl = str_replace('https://', '', $domainUrl);
        $domainUrlArray = explode('/', $domainUrl);
        $domainUrl = $domainUrlArray[0].'/';

        $reviewSite = $this->repoReviewSite->getByDomain($domainUrl);

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
