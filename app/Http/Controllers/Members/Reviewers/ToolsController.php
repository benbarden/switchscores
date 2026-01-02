<?php

namespace App\Http\Controllers\Members\Reviewers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class ToolsController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private ReviewDraftRepository $repoReviewDraft,
        private ReviewSiteRepository $repoReviewSite
    )
    {
    }

    public function importReviews()
    {
        $pageTitle = 'Import reviews';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

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
            return view('members.reviewers.tools.importReviews.process', $bindings);
        } else {
            return view('members.reviewers.tools.importReviews.landing', $bindings);
        }

    }
}
