<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class ToolsController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private ReviewDraftRepository $repoReviewDraft,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function importReviews()
    {
        $pageTitle = 'Import reviews';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $siteId = $currentUser->partner_id;
        if (!$siteId) abort(403);

        $partnerData = $this->repoReviewSite->find($siteId);

        $bindings['DraftsForProcessing'] = $this->repoReviewDraft->getReadyForProcessingBySite($siteId);

        if (request()->post()) {
            \Artisan::call('ReviewConvertDraftsToReviews '.$siteId, []);
            \Artisan::call('ReviewSiteUpdateStats', []);
            \Artisan::call('UpdateGameRanks', []);
            \Artisan::call('ReviewCampaignUpdateProgress', []);
            \Artisan::call('UpdateGameReviewStats', []);
            return view('reviewers.tools.importReviews.process', $bindings);
        } else {
            return view('reviewers.tools.importReviews.landing', $bindings);
        }

    }
}
