<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;

use App\Traits\SwitchServices;

class FeedHealthController extends Controller
{
    use SwitchServices;

    protected $repoReviewSite;
    protected $repoPartnerFeedLink;
    protected $repoReviewDraft;

    public function __construct(
        ReviewSiteRepository $repoReviewSite,
        PartnerFeedLinkRepository $repoPartnerFeedLink,
        ReviewDraftRepository $repoReviewDraft
    )
    {
        $this->repoReviewSite = $repoReviewSite;
        $this->repoPartnerFeedLink = $repoPartnerFeedLink;
        $this->repoReviewDraft = $repoReviewDraft;
    }

    public function landing()
    {
        $bindings = [];

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $partnerFeedLink = $this->repoPartnerFeedLink->firstBySite($partnerId);
        $bindings['PartnerFeedLink'] = $partnerFeedLink;

        $pageTitle = 'Feed health';

        $bindings['ImportStatsFailuresList'] = $this->repoReviewDraft->getFailedImportStatsBySite($partnerId);

        $bindings['SuccessFailStats'] = $this->repoReviewDraft->getSuccessFailStatsBySite($partnerId);

        $bindings['ParseStatusStats'] = $this->repoReviewDraft->getParseStatusStatsBySite($partnerId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.feed-health.landing', $bindings);
    }

    public function byProcessStatus($status)
    {
        $bindings = [];

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Feed health - view by process status';

        $bindings['ReviewFeedItems'] = $this->repoReviewDraft->getByProcessStatusAndSite($status, $partnerId);
        $bindings['StatusDesc'] = $status;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.feed-health.byProcessStatus', $bindings);
    }

    public function byParseStatus($status)
    {
        $tableLimit = 50;

        $bindings = [];

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Feed health - view by parse status';

        $bindings['ReviewFeedItems'] = $this->repoReviewDraft->getByParseStatusAndSite($status, $partnerId, $tableLimit);
        $bindings['StatusDesc'] = $status;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['TableLimit'] = $tableLimit;

        return view('reviewers.feed-health.byParseStatus', $bindings);
    }
}
