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

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        // Data cleanup: old fields
        $bindings['GamesWithOldDevFieldSetCount'] = $serviceGameDeveloper->countGamesWithOldDevFieldSet();
        $bindings['GamesWithOldPubFieldSetCount'] = $serviceGamePublisher->countGamesWithOldPubFieldSet();

        // Data cleanup
        $legacyDevMultipleCount = count($serviceGame->getOldDevelopersMultiple());
        $legacyPubMultipleCount = count($serviceGame->getOldPublishersMultiple());
        $bindings['LegacyPartnerMultipleCount'] = $legacyDevMultipleCount + $legacyPubMultipleCount;
        $legacyDeveloperNoGamesCompanyCount = count($servicePartner->getUnmatchedGameDevelopers());
        $bindings['LegacyDeveloperNoGamesCompanyCount'] = $legacyDeveloperNoGamesCompanyCount;
        $legacyPublisherNoGamesCompanyCount = count($servicePartner->getUnmatchedGamePublishers());
        $bindings['LegacyPublisherNoGamesCompanyCount'] = $legacyPublisherNoGamesCompanyCount;

        // Outreach
        $devsWithUnrankedGames = $servicePartner->getDevelopersWithUnrankedGames();
        $bindings['DevsWithUnrankedGamesCount'] = count($devsWithUnrankedGames);
        $pubsWithUnrankedGames = $servicePartner->getPublishersWithUnrankedGames();
        $bindings['PubsWithUnrankedGamesCount'] = count($pubsWithUnrankedGames);

        // Action lists
        $bindings['DeveloperMissingCount'] = $serviceGameDeveloper->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $serviceGamePublisher->countGamesWithNoPublisher();

        return view('staff.partners.dashboard', $bindings);
    }
}
