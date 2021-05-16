<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Partner;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class PartnersController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Partners');

        $servicePartner = $this->getServicePartner();
        $reviewPartnerList = $servicePartner->getReviewSitesWithRecentReviews();
        $bindings['ReviewPartnerList'] = $reviewPartnerList;

        $bindings['TopTitle'] = 'Partners';
        $bindings['PageTitle'] = 'Partners';

        return view('partners.landing', $bindings);
    }

    public function guidesShow($guideTitle)
    {
        $bindings = [];

        $guide = [];
        $guideView = '';

        switch ($guideTitle) {
            case 'new-review-site-welcome':
                $guide['title'] = 'New review site welcome guide';
                $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($guide['title']);
                $guideView = 'partners.guides.newReviewSiteWelcome';
                break;
            default:
                abort(404);
        }

        $bindings['TopTitle'] = $guide['title'];
        $bindings['PageTitle'] = $guide['title'];

        return view($guideView, $bindings);
    }

    public function reviewSites()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage('Review partners');

        $bindings['TopTitle'] = 'Review partners';
        $bindings['PageTitle'] = 'Review partners';

        return view('partners.reviewSites', $bindings);
    }

    public function developersPublishers()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage('Developers and publishers');

        $bindings['TopTitle'] = 'Developers and Publishers';
        $bindings['PageTitle'] = 'Developers and Publishers';

        return view('partners.developersPublishers', $bindings);
    }

    public function showGamesCompany($linkTitle)
    {
        $servicePartner = $this->getServicePartner();

        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $partnerData = $servicePartner->getByLinkTitle($linkTitle);
        if (!$partnerData) abort(404);
        if ($partnerData->type_id != Partner::TYPE_GAMES_COMPANY) abort(404);

        $partnerId = $partnerData->id;

        $gameDevList = $serviceGameDeveloper->getGamesByDeveloper($partnerId, false);
        $gamePubList = $serviceGamePublisher->getGamesByPublisher($partnerId, false);

        $mergedGameList = $servicePartner->getMergedGameList($gameDevList, $gamePubList);
        $mergedGameList = collect($mergedGameList)->sortBy('eu_release_date')->reverse()->toArray();

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($partnerData->name);

        $bindings['TopTitle'] = $partnerData->name;
        $bindings['PageTitle'] = $partnerData->name;

        $bindings['PartnerData'] = $partnerData;
        $bindings['PartnerId'] = $partnerId;

        //$bindings['GameDevList'] = $gameDevList;
        //$bindings['GamePubList'] = $gamePubList;
        $bindings['MergedGameList'] = $mergedGameList;

        return view('partners.detail.gamesCompany', $bindings);
    }
}
