<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ReviewSite\Repository as ReviewSiteRepository;
use App\Domain\PartnerFeedLink\Repository as PartnerFeedLinkRepository;
use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\ReviewDraft\Repository as ReviewDraftRepository;
use App\Domain\Unranked\Repository as UnrankedRepository;

use App\Traits\SwitchServices;

class IndexController extends Controller
{
    use SwitchServices;

    private $repoReviewSite;
    private $repoPartnerFeedLink;
    private $repoCampaign;
    private $repoReviewDraft;
    private $repoUnranked;

    public function __construct(
        ReviewSiteRepository $repoReviewSite,
        PartnerFeedLinkRepository $repoPartnerFeedLink,
        CampaignRepository $repoCampaign,
        ReviewDraftRepository $repoReviewDraft,
        UnrankedRepository $repoUnranked
    )
    {
        $this->repoReviewSite = $repoReviewSite;
        $this->repoPartnerFeedLink = $repoPartnerFeedLink;
        $this->repoCampaign = $repoCampaign;
        $this->repoReviewDraft = $repoReviewDraft;
        $this->repoUnranked = $repoUnranked;
    }

    public function show()
    {
        $currentUser = resolve('User/Repository')->currentUser();

        $reviewSite = $currentUser->partner;
        // These shouldn't be possible but it saves problems later on
        if (!$reviewSite) abort(403);

        $partnerId = $reviewSite->id;
        $partnerUrl = $reviewSite->website_url;

        $serviceReviewLink = $this->getServiceReviewLink();

        $bindings = [];

        $bindings['PartnerData'] = $reviewSite;

        $pageTitle = 'Reviewers dashboard: '.$reviewSite->name;

        // Review stats (for infobox)
        $reviewStats = $serviceReviewLink->getSiteReviewStats($partnerId);
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);

        // Recent reviews
        $bindings['SiteReviewsLatest'] = $serviceReviewLink->getLatestBySite($partnerId, 10);

        // Feed items
        $bindings['PartnerFeed'] = $this->repoPartnerFeedLink->firstBySite($partnerId);
        $bindings['ReviewDraftsPending'] = $this->repoReviewDraft->getUnprocessedBySite($partnerId);
        $bindings['ReviewDraftsSuccess'] = $this->repoReviewDraft->getSuccessBySite($partnerId, 10);
        $bindings['ReviewDraftsFailed'] = $this->repoReviewDraft->getFailedBySite($partnerId, 10);

        // Unranked
        $allowedYears = resolve('Domain\GameCalendar\AllowedDates')->releaseYears();
        $bindings['AllowedYears'] = $allowedYears;
        $bindings['UnrankedReviews2'] = $this->repoUnranked->totalByReviewCount(2);
        $bindings['UnrankedReviews1'] = $this->repoUnranked->totalByReviewCount(1);
        $bindings['UnrankedReviews0'] = $this->repoUnranked->totalByReviewCount(0);
        foreach ($allowedYears as $year) {
            $bindings['UnrankedYear'.$year] = $this->repoUnranked->totalByYear($year);
        }
        $bindings['UnrankedLowQuality'] = $this->repoUnranked->totalLowQuality();

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
