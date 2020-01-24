<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesListController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function landing($report)
    {
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $partnerId = $authUser->partner_id;
        if (!$partnerId) abort(403);

        $partner = $servicePartner->find($partnerId);
        if (!$partner) abort(403);

        if (!$partner->isGamesCompany()) abort(403);

        $bindings['PartnerData'] = $partner;

        // Games
        if (!in_array($report, ['developer', 'publisher'])) abort(404);

        if ($report == 'developer') {
            $gamesList = $serviceGameDeveloper->getGamesByDeveloper($partnerId, false);
        } elseif ($report == 'publisher') {
            $gamesList = $serviceGamePublisher->getGamesByPublisher($partnerId, false);
        }

        $bindings['PartnerGameList'] = $gamesList;
        $bindings['jsInitialSort'] = "[ 1, 'desc']";

        $bindings['TopTitle'] = 'Games list';
        $bindings['PageTitle'] = 'Games list';

        return view('user.games-list.list', $bindings);
    }
}
