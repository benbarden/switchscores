<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class GamesListController extends Controller
{
    public function landing($report)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePartner = $serviceContainer->getPartnerService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $bindings = [];

        $region = Auth::user()->region;
        $bindings['UserRegion'] = $region;

        $userId = Auth::id();

        $authUser = Auth::user();

        $partnerId = $authUser->partner_id;

        if (!$partnerId) abort(403);

        $partner = $servicePartner->find($partnerId);

        if (!$partner) abort(403);

        if (!$partner->isGamesCompany()) abort(403);

        $bindings['PartnerData'] = $partner;

        // Games
        if (!in_array($report, ['developer', 'publisher'])) abort(404);

        if ($report == 'developer') {
            $gamesList = $serviceGameDeveloper->getGamesByDeveloper($region, $partnerId);
        } elseif ($report == 'publisher') {
            $gamesList = $serviceGamePublisher->getGamesByPublisher($region, $partnerId);
        }

        $bindings['PartnerGameList'] = $gamesList;
        $bindings['jsInitialSort'] = "[ 1, 'desc']";

        $bindings['TopTitle'] = 'Games list';
        $bindings['PageTitle'] = 'Games list';

        return view('user.games-list.list', $bindings);
    }
}
