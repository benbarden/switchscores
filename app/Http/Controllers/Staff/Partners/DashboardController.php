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

        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        // Data cleanup
        $legacyDevMultipleCount = count($serviceGame->getOldDevelopersMultiple());
        $legacyPubMultipleCount = count($serviceGame->getOldPublishersMultiple());
        $bindings['LegacyPartnerMultipleCount'] = $legacyDevMultipleCount + $legacyPubMultipleCount;
        $legacyDeveloperNoGamesCompanyCount = count($servicePartner->getUnmatchedGameDevelopers());
        $bindings['LegacyDeveloperNoGamesCompanyCount'] = $legacyDeveloperNoGamesCompanyCount;
        $legacyPublisherNoGamesCompanyCount = count($servicePartner->getUnmatchedGamePublishers());
        $bindings['LegacyPublisherNoGamesCompanyCount'] = $legacyPublisherNoGamesCompanyCount;

        // Action lists
        $bindings['DeveloperMissingCount'] = $serviceGameDeveloper->countGamesWithNoDeveloper();
        $bindings['GamesWithOldDevFieldSetCount'] = $serviceGameDeveloper->countGamesWithOldDevFieldSet();
        $bindings['PublisherMissingCount'] = $serviceGamePublisher->countGamesWithNoPublisher();
        $bindings['GamesWithOldPubFieldSetCount'] = $serviceGamePublisher->countGamesWithOldPubFieldSet();

        // Stats
        $bindings['GameDeveloperLinks'] = $serviceGameDeveloper->countGameDeveloperLinks();
        $bindings['GamePublisherLinks'] = $serviceGamePublisher->countGamePublisherLinks();

        return view('staff.partners.dashboard', $bindings);
    }
}
