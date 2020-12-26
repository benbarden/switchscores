<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class DashboardController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function show()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Partners dashboard');

        // Action lists
        $bindings['DeveloperMissingCount'] = $this->getServiceGameDeveloper()->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $this->getServiceGamePublisher()->countGamesWithNoPublisher();

        $bindings['GamesCompaniesWithoutWebsiteUrlsCount'] = $this->getServicePartner()->countGamesCompaniesWithoutWebsiteUrls();
        $bindings['GamesCompaniesWithoutTwitterIdsCount'] = $this->getServicePartner()->countGamesCompaniesWithoutTwitterIds();

        $duplicateTwitterIdsList = $this->getServicePartner()->getGamesCompanyDuplicateTwitterIds();
        $duplicateWebsiteUrlsList = $this->getServicePartner()->getGamesCompanyDuplicateWebsiteUrls();
        $bindings['GamesCompanyDuplicateTwitterIdsCount'] = count($duplicateTwitterIdsList);
        $bindings['GamesCompanyDuplicateWebsiteUrlsCount'] = count($duplicateWebsiteUrlsList);

        // Outreach
        $devsWithUnrankedGames = $this->getServicePartner()->getDevelopersWithUnrankedGames();
        $bindings['DevsWithUnrankedGamesCount'] = count($devsWithUnrankedGames);
        $pubsWithUnrankedGames = $this->getServicePartner()->getPublishersWithUnrankedGames();
        $bindings['PubsWithUnrankedGamesCount'] = count($pubsWithUnrankedGames);

        return view('staff.partners.dashboard', $bindings);
    }
}
