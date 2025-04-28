<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class ReviewLinkController extends Controller
{
    public function __construct(
        private ReviewLinkRepository $repoReviewLink,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function landing($report = '')
    {
        $pageTitle = 'Review links';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

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
                $reviewLinks = $this->repoReviewLink->bySiteScore($partnerId, $rating);
                $bindings['FilterType'] = 'by-score';
                $bindings['FilterName'] = $report;
                $bindings['FilterValue'] = $rating;
                break;
            default:
                $reviewLinks = $this->repoReviewLink->bySite($partnerId);
                break;
        }

        $bindings['ReviewLinks'] = $reviewLinks;
        $bindings['jsInitialSort'] = "[ 3, 'desc']";

        return view('reviewers.reviews.list', $bindings);
    }
}
