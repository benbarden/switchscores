<?php

namespace App\Http\Controllers\Staff\Partners;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class DataCleanupController extends Controller
{
    use SwitchServices;

    public function gamesWithMissingDeveloper()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $pageTitle = 'Games with missing developer';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGameDeveloper->getGamesWithNoDeveloper();
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        return view('staff.partners.data-cleanup.games-with-missing-developer', $bindings);
    }

    public function gamesWithMissingPublisher()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $pageTitle = 'Games with missing publisher';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGamePublisher->getGamesWithNoPublisher();
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        return view('staff.partners.data-cleanup.games-with-missing-publisher', $bindings);
    }

    public function legacyPartnerMultiple()
    {
        $serviceGame = $this->getServiceGame();

        $pageTitle = 'Legacy partners with multiple records';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['LegacyDevMultipleList'] = $serviceGame->getOldDevelopersMultiple();
        $bindings['LegacyPubMultipleList'] = $serviceGame->getOldPublishersMultiple();

        return view('staff.partners.data-cleanup.legacy-partner-multiple', $bindings);
    }

    public function legacyDeveloperNoGamesCompany()
    {
        $servicePartner = $this->getServicePartner();

        $pageTitle = 'Legacy developers - No games company';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $servicePartner->getUnmatchedGameDevelopers();

        return view('staff.partners.data-cleanup.legacy-developer-no-games-company', $bindings);
    }

    public function legacyDeveloperNoGamesCompanyGameList($developer)
    {
        $serviceGame = $this->getServiceGame();

        $pageTitle = 'Legacy developers - No games company - Game list';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGame->getByDeveloper($developer);
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        return view('staff.partners.data-cleanup.legacy-developer-game-list', $bindings);
    }

    public function legacyPublisherNoGamesCompany()
    {
        $servicePartner = $this->getServicePartner();

        $pageTitle = 'Legacy publishers - No games company';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $servicePartner->getUnmatchedGamePublishers();

        return view('staff.partners.data-cleanup.legacy-publisher-no-games-company', $bindings);
    }

    public function legacyPublisherNoGamesCompanyGameList($publisher)
    {
        $serviceGame = $this->getServiceGame();

        $pageTitle = 'Legacy publishers - No games company - Game list';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGame->getByPublisher($publisher);
        $bindings['jsInitialSort'] = "[ 1, 'asc']";

        return view('staff.partners.data-cleanup.legacy-publisher-game-list', $bindings);
    }

    public function gamesWithOldDevFieldSet()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $pageTitle = 'Games with old dev field set';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGameDeveloper->getGamesWithOldDevFieldSet();
        $bindings['jsInitialSort'] = "[ 2, 'asc']";

        return view('staff.partners.data-cleanup.games-with-old-dev-field-set', $bindings);
    }

    public function gamesWithOldPubFieldSet()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $pageTitle = 'Games with old pub field set';

        $bindings = [];

        $bindings['PageTitle'] = $pageTitle;
        $bindings['TopTitle'] = $pageTitle.' - Partners - Staff';

        $bindings['ItemList'] = $serviceGamePublisher->getGamesWithOldPubFieldSet();
        $bindings['jsInitialSort'] = "[ 2, 'asc']";

        return view('staff.partners.data-cleanup.games-with-old-pub-field-set', $bindings);
    }

}
