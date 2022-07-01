<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Models\ReviewDraft;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\ReviewDraft\Builder as ReviewDraftBuilder;
use App\Domain\ReviewDraft\Director as ReviewDraftDirector;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class ToolsController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $repoReviewDraft;

    protected $repoReviewSite;

    public function __construct(
        ReviewDraftRepository $repoReviewDraft,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->repoReviewDraft = $repoReviewDraft;
        $this->repoReviewSite = $repoReviewSite;
    }

    public function importReviews()
    {
        $bindings = [];

        $siteId = $this->getCurrentUserReviewSiteId();
        if (!$siteId) abort(403);

        $bindings['PageTitle'] = "Import reviews";
        $bindings['TopTitle'] = "Import reviews";

        $partnerData = $this->repoReviewSite->find($siteId);

        if (request()->post()) {
            \Artisan::call('ReviewConvertDraftsToReviews '.$siteId, []);
            \Artisan::call('PartnerUpdateFields', []);
            \Artisan::call('UpdateGameRanks', []);
            \Artisan::call('ReviewCampaignUpdateProgress', []);
            \Artisan::call('UpdateGameReviewStats', []);
            return view('reviewers.tools.importReviews.process', $bindings);
        } else {
            return view('reviewers.tools.importReviews.landing', $bindings);
        }

    }
}
