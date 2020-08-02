<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'Partners dashboard';

        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        // Action lists
        $bindings['GamesCompaniesWithoutWebsiteUrls'] = $servicePartner->countGamesCompaniesWithoutWebsiteUrls();
        $bindings['GamesCompaniesWithoutTwitterIds'] = $servicePartner->countGamesCompaniesWithoutTwitterIds();

        // Outreach
        $devsWithUnrankedGames = $servicePartner->getDevelopersWithUnrankedGames();
        $bindings['DevsWithUnrankedGamesCount'] = count($devsWithUnrankedGames);
        $pubsWithUnrankedGames = $servicePartner->getPublishersWithUnrankedGames();
        $bindings['PubsWithUnrankedGamesCount'] = count($pubsWithUnrankedGames);

        return view('staff.partners.dashboard', $bindings);
    }
}
