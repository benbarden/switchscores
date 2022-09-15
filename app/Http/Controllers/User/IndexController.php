<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Campaign\Repository as CampaignRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\MemberView;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    protected $repoCampaign;
    protected $repoGamesCompany;

    public function __construct(
        CampaignRepository $repoCampaign,
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->repoCampaign = $repoCampaign;
        $this->repoGamesCompany = $repoGamesCompany;
    }

    public function show()
    {
        $onPageTitle = 'Members dashboard';

        $bindings = $this->getBindingsDashboardGenericSubpage($onPageTitle);

        $siteRole = 'member'; // default

        $userId = $this->getAuthId();
        $authUser = $this->getValidUser($this->getServiceUser());

        $bindings['SiteRole'] = $siteRole;
        $bindings['UserData'] = $authUser;
        $bindings['TotalGames'] = $this->getServiceUserGamesCollection()->getUserTotalGames($userId);
        $bindings['TotalHours'] = $this->getServiceUserGamesCollection()->getUserTotalHours($userId);

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
