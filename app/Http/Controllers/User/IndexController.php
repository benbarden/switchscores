<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\UserGamesCollection\CollectionStatsRepository;

use App\Traits\SwitchServices;

class IndexController extends Controller
{
    use SwitchServices;

    protected $repoCampaign;
    protected $repoGamesCompany;
    protected $repoCollectionStats;

    public function __construct(
        CampaignRepository $repoCampaign,
        GamesCompanyRepository $repoGamesCompany,
        CollectionStatsRepository $repoCollectionStats
    )
    {
        $this->repoCampaign = $repoCampaign;
        $this->repoGamesCompany = $repoGamesCompany;
        $this->repoCollectionStats = $repoCollectionStats;
    }

    public function show()
    {
        $pageTitle = 'Members dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $siteRole = 'member'; // default

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['SiteRole'] = $siteRole;
        $bindings['UserData'] = $currentUser;
        $bindings['TotalGames'] = $this->repoCollectionStats->userTotalGames($userId);
        $bindings['TotalHours'] = $this->repoCollectionStats->userTotalHours($userId);

        // Campaigns
        $activeCampaigns = $this->repoCampaign->getActive();
        foreach ($activeCampaigns as &$item) {
            $campaignId = $item->id;
            $item['ranked_count'] = $this->getServiceCampaignGame()->countRankedGames($campaignId);
        }
        $bindings['ActiveCampaigns'] = $activeCampaigns;

        return view('user.index', $bindings);
    }
}
