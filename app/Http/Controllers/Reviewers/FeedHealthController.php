<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class FeedHealthController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function landing()
    {
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $partnerData = $servicePartner->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$partnerData) abort(400);
        if (!$partnerData->isReviewSite()) abort(500);

        $bindings['PartnerData'] = $partnerData;

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
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $partnerData = $servicePartner->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$partnerData) abort(400);
        if (!$partnerData->isReviewSite()) abort(500);

        $bindings['PartnerData'] = $partnerData;

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

        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $partnerData = $servicePartner->find($partnerId);

        // These shouldn't be possible but it saves problems later on
        if (!$partnerData) abort(400);
        if (!$partnerData->isReviewSite()) abort(500);

        $bindings['PartnerData'] = $partnerData;

        $pageTitle = 'Feed health - view by parse status';

        $bindings['ReviewFeedItems'] = $this->getServiceReviewFeedItem()->getByParseStatusAndSite($status, $partnerId, $tableLimit);
        $bindings['StatusDesc'] = $status;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['TableLimit'] = $tableLimit;

        return view('reviewers.feed-health.byParseStatus', $bindings);
    }
}
