<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\ReviewSite\Repository as ReviewSiteRepository;

use App\Models\Partner;
use App\Traits\SwitchServices;

class PartnersController extends Controller
{
    use SwitchServices;

    protected $viewBreadcrumbs;
    protected $repoGamesCompany;
    protected $repoReviewSite;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        GamesCompanyRepository $repoGamesCompany,
        ReviewSiteRepository $repoReviewSite
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->repoReviewSite = $repoReviewSite;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->topLevelPage('Partners');

        $bindings['ReviewPartnerList'] = $this->repoReviewSite->getActive();

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

    public function showReviewSite($linkTitle)
    {
        $bindings = [];

        $serviceReviewLink = $this->getServiceReviewLink();

        $reviewSite = $this->repoReviewSite->getByLinkTitle($linkTitle);

        if (!$reviewSite) {
            abort(404);
        }

        $siteId = $reviewSite->id;

        $bindings['TopTitle'] = $reviewSite->name.' - Site profile';
        $bindings['PageTitle'] = $reviewSite->name.' - Site profile';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->reviewsSubpage($reviewSite->name.' - Site profile');

        $bindings['PartnerData'] = $reviewSite;

        $siteReviewsLatest = $serviceReviewLink->getLatestBySite($siteId);
        $reviewStats = $serviceReviewLink->getSiteReviewStats($siteId);
        $reviewScoreDistribution = $serviceReviewLink->getSiteScoreDistribution($siteId);

        $mostUsedScore = ['topScore' => 0, 'topScoreCount' => 0];
        if ($reviewScoreDistribution) {
            foreach ($reviewScoreDistribution as $scoreKey => $scoreVal) {
                if ($scoreVal > $mostUsedScore['topScoreCount']) {
                    $mostUsedScore = ['topScore' => $scoreKey, 'topScoreCount' => $scoreVal];
                }
            }
        }

        $bindings['SiteReviewsLatest'] = $siteReviewsLatest;
        $bindings['ReviewAvg'] = round($reviewStats[0]->ReviewAvg, 2);
        $bindings['ReviewScoreDistribution'] = $reviewScoreDistribution;
        $bindings['MostUsedScore'] = $mostUsedScore;

        return view('partners.detail.reviewSite', $bindings);
    }

    public function showGamesCompany($linkTitle)
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $gamesCompany = $this->repoGamesCompany->getByLinkTitle($linkTitle);
        if (!$gamesCompany) abort(404);

        $gamesCompanyId = $gamesCompany->id;

        $gameDevList = $serviceGameDeveloper->getGamesByDeveloper($gamesCompanyId, false);
        $gamePubList = $serviceGamePublisher->getGamesByPublisher($gamesCompanyId, false);

        $mergedGameList = $this->repoGamesCompany->getMergedGameList($gameDevList, $gamePubList);
        $mergedGameList = collect($mergedGameList)->sortBy('eu_release_date')->reverse()->toArray();

        $bindings = [];

        $bindings['crumbNav'] = $this->viewBreadcrumbs->partnersSubpage($gamesCompany->name);

        $bindings['TopTitle'] = $gamesCompany->name;
        $bindings['PageTitle'] = $gamesCompany->name;

        $bindings['PartnerData'] = $gamesCompany;
        $bindings['PartnerId'] = $gamesCompanyId;

        //$bindings['GameDevList'] = $gameDevList;
        //$bindings['GamePubList'] = $gamePubList;
        $bindings['MergedGameList'] = $mergedGameList;

        return view('partners.detail.gamesCompany', $bindings);
    }
}
