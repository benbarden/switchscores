<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class ReviewLinkController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoReviewSite;

    public function __construct(
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoReviewSite = $repoReviewSite;
    }

    public function landing($report = '')
    {
        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        if (!$partnerId) abort(403);

        $reviewSite = $this->repoReviewSite->find($partnerId);

        if (!$reviewSite) abort(403);

        $bindings['ReviewSite'] = $reviewSite;

        switch ($report) {
            case 'score-1':
            case 'score-2':
            case 'score-3':
            case 'score-4':
            case 'score-5':
            case 'score-6':
            case 'score-7':
            case 'score-8':
            case 'score-9':
            case 'score-10':
                $rating = str_replace('score-', '', $report);
                $reviewLinks = $serviceReviewLink->getBySiteScore($partnerId, $rating);
                $bindings['FilterType'] = 'by-score';
                $bindings['FilterName'] = $report;
                $bindings['FilterValue'] = $rating;
                break;
            default:
                $reviewLinks = $serviceReviewLink->getAllBySite($partnerId);
                break;
        }

        $bindings['ReviewLinks'] = $reviewLinks;
        $bindings['jsInitialSort'] = "[ 3, 'desc']";

        $bindings['TopTitle'] = 'Review links';
        $bindings['PageTitle'] = 'Review links';

        return view('reviewers.reviews.list', $bindings);
    }
}
