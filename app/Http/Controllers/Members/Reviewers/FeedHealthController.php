<?php

namespace App\Http\Controllers\Members\Reviewers;

use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use Illuminate\Routing\Controller as Controller;

class FeedHealthController extends Controller
{
    public function __construct(
        private ReviewSiteRepository $repoReviewSite,
        private PartnerFeedLinkRepository $repoPartnerFeedLink,
        private ReviewDraftRepository $repoReviewDraft
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Feed health';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $partnerFeedLink = $this->repoPartnerFeedLink->firstBySite($partnerId);
        $bindings['PartnerFeedLink'] = $partnerFeedLink;

        $bindings['ImportStatsFailuresList'] = $this->repoReviewDraft->getFailedImportStatsBySite($partnerId);

        $bindings['SuccessFailStats'] = $this->repoReviewDraft->getSuccessFailStatsBySite($partnerId);

        $bindings['ParseStatusStats'] = $this->repoReviewDraft->getParseStatusStatsBySite($partnerId);

        return view('members.reviewers.feed-health.landing', $bindings);
    }

    public function byProcessStatus($status)
    {
        $pageTitle = 'Feed health - by process status';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $bindings['ReviewFeedItems'] = $this->repoReviewDraft->getByProcessStatusAndSite($status, $partnerId);
        $bindings['StatusDesc'] = $status;

        return view('members.reviewers.feed-health.byProcessStatus', $bindings);
    }

    public function byParseStatus($status)
    {
        $pageTitle = 'Feed health - by parse status';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->reviewersSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $tableLimit = 50;

        $currentUser = resolve('User/Repository')->currentUser();

        $partnerId = $currentUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $bindings['ReviewFeedItems'] = $this->repoReviewDraft->getByParseStatusAndSite($status, $partnerId, $tableLimit);
        $bindings['StatusDesc'] = $status;

        $bindings['TableLimit'] = $tableLimit;

        return view('members.reviewers.feed-health.byParseStatus', $bindings);
    }
}
