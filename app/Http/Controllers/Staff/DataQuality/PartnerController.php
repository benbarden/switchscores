<?php

namespace App\Http\Controllers\Staff\DataQuality;

use Illuminate\Routing\Controller as Controller;

use App\Services\DataQuality\QualityStats;

use App\Traits\SwitchServices;

class PartnerController extends Controller
{
    use SwitchServices;

    public function dashboard()
    {
        $serviceQualityStats = new QualityStats();

        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $pageTitle = 'Partner dashboard';

        $breadcrumbs = $this->getServiceViewHelperBreadcrumbs()->makeDataQualitySubPage($pageTitle);

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitleSuffix('Data quality')
            ->setBreadcrumbs($breadcrumbs)
            ->getBindings();

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

        // Action lists
        $bindings['DeveloperMissingCount'] = $serviceGameDeveloper->countGamesWithNoDeveloper();
        $bindings['PublisherMissingCount'] = $serviceGamePublisher->countGamesWithNoPublisher();

        return view('staff.data-quality.partner.dashboard', $bindings);
    }
}
