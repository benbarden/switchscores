<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

use App\Domain\Campaign\Repository as CampaignRepository;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoCampaign;

    public function __construct(
        CampaignRepository $repoCampaign
    )
    {
        $this->repoCampaign = $repoCampaign;
    }

    public function show()
    {
        $servicePartner = $this->getServicePartner();
        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $partnerData = $servicePartner->find($partnerId);
        $partnerUrl = $partnerData->website_url;

        // These shouldn't be possible but it saves problems later on
        if (!$partnerData) abort(400);
        if (!$partnerData->isReviewSite()) abort(500);

        $bindings['PartnerData'] = $partnerData;

        $pageTitle = 'Reviewers dashboard: '.$partnerData->name;

        // Review stats (for infobox)
        $reviewStats = $serviceReviewLink->getSiteReviewStats($partnerId);
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

        // Recent reviews
        $bindings['SiteReviewsLatest'] = $serviceReviewLink->getLatestBySite($partnerId, 10);

        // Feed items
        $bindings['PartnerFeed'] = $this->getServicePartnerFeedLink()->getBySite($partnerId);
        $bindings['FeedItemsLatest'] = $this->getServiceReviewFeedItem()->getAllBySite($partnerId, 5);
        $bindings['FeedItemsPending'] = $this->getServiceReviewFeedItem()->getUnprocessedBySite($partnerId);
        $bindings['FeedItemsSuccess'] = $this->getServiceReviewFeedItem()->getSuccessBySite($partnerId, 5);
        $bindings['FeedItemsFailed'] = $this->getServiceReviewFeedItem()->getFailedBySite($partnerId, 5);

        // Campaigns
        $activeCampaigns = $this->repoCampaign->getActive();
        foreach ($activeCampaigns as &$item) {
            $campaignId = $item->id;
            $item['ranked_count'] = $this->getServiceCampaignGame()->countRankedGames($campaignId);
        }
        $bindings['ActiveCampaigns'] = $activeCampaigns;

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $youtubeBaseLink = 'https://youtube.com/';
        if (substr($partnerUrl, 0, strlen('https://youtube.com/')) == $youtubeBaseLink) {
            $isYoutubeChannel = true;
        } else {
            $isYoutubeChannel = false;
        }
        $bindings['IsYoutubeChannel'] = $isYoutubeChannel;

        return view('reviewers.index', $bindings);
    }
}
