<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\Campaign\Repository as CampaignRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;

    protected $repoReviewSite;
    protected $repoPartnerFeedLink;
    protected $repoCampaign;

    public function __construct(
        ReviewSiteRepository $repoReviewSite,
        PartnerFeedLinkRepository $repoPartnerFeedLink,
        CampaignRepository $repoCampaign
    )
    {
        $this->repoReviewSite = $repoReviewSite;
        $this->repoPartnerFeedLink = $repoPartnerFeedLink;
        $this->repoCampaign = $repoCampaign;
    }

    public function show()
    {
        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;

        $reviewSite = $this->repoReviewSite->find($partnerId);
        $partnerUrl = $reviewSite->website_url;

        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(400);

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Reviewers dashboard: '.$reviewSite->name;

        // Review stats (for infobox)
        $reviewStats = $serviceReviewLink->getSiteReviewStats($partnerId);
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

        // Recent reviews
        $bindings['SiteReviewsLatest'] = $serviceReviewLink->getLatestBySite($partnerId, 10);

        // Feed items
        $bindings['PartnerFeed'] = $this->repoPartnerFeedLink->firstBySite($partnerId);
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

        if (request()->action == 'newsignup') {
            $bindings['ShowNewSignup'] = true;
        }

        return view('reviewers.index', $bindings);
    }
}
