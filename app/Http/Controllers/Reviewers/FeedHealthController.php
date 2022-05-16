<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class FeedHealthController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoReviewSite;
    protected $repoPartnerFeedLink;

    public function __construct(
        ReviewSiteRepository $repoReviewSite,
        PartnerFeedLinkRepository $repoPartnerFeedLink
    )
    {
        $this->repoReviewSite = $repoReviewSite;
        $this->repoPartnerFeedLink = $repoPartnerFeedLink;
    }

    public function landing()
    {
        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $partnerFeedLink = $this->repoPartnerFeedLink->firstBySite($partnerId);
        $bindings['PartnerFeedLink'] = $partnerFeedLink;

        $pageTitle = 'Feed health';

        $bindings['ImportStatsFailuresList'] = $this->getServiceReviewFeedItem()->getFailedImportStatsBySite($partnerId);

        $bindings['SuccessFailStats'] = $this->getServiceReviewFeedItem()->getSuccessFailStatsBySite($partnerId);

        $bindings['ParseStatusStats'] = $this->getServiceReviewFeedItem()->getParseStatusStatsBySite($partnerId);

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.feed-health.landing', $bindings);
    }

    public function byProcessStatus($status)
    {
        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Feed health - view by process status';

        $bindings['ReviewFeedItems'] = $this->getServiceReviewFeedItem()->getByProcessStatusAndSite($status, $partnerId);
        $bindings['StatusDesc'] = $status;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('reviewers.feed-health.byProcessStatus', $bindings);
    }

    public function byParseStatus($status)
    {
        $tableLimit = 50;

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Feed health - view by parse status';

        $bindings['ReviewFeedItems'] = $this->getServiceReviewFeedItem()->getByParseStatusAndSite($status, $partnerId, $tableLimit);
        $bindings['StatusDesc'] = $status;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['TableLimit'] = $tableLimit;

        return view('reviewers.feed-health.byParseStatus', $bindings);
    }
}
