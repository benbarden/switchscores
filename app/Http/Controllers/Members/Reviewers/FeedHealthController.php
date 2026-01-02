<?php

namespace App\Http\Controllers\Members\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

class FeedHealthController extends Controller
{
    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private ReviewSiteRepository $repoReviewSite,
        private PartnerFeedLinkRepository $repoPartnerFeedLink,
        private ReviewDraftRepository $repoReviewDraft
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Feed health';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

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
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

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
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::reviewersSubpage($pageTitle))->bindings;

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
