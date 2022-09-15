<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesListController extends Controller
{
    use SwitchServices;
    use AuthUser;

    private $repoGamesCompany;

    public function __construct(
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->repoGamesCompany = $repoGamesCompany;
    }

    public function landing($report)
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $bindings = [];

        $userId = $this->getAuthId();

        $authUser = $this->getValidUser($this->getServiceUser());

        $gamesCompanyId = $authUser->partner_id;
        if (!$gamesCompanyId) abort(403);

        $gamesCompany = $this->repoGamesCompany->find($gamesCompanyId);
        if (!$gamesCompany) abort(403);

        $bindings['PartnerData'] = $gamesCompany;

        // Games
        if (!in_array($report, ['developer', 'publisher'])) abort(404);

        if ($report == 'developer') {
            $gamesList = $serviceGameDeveloper->getGamesByDeveloper($gamesCompanyId, false);
        } elseif ($report == 'publisher') {
            $gamesList = $serviceGamePublisher->getGamesByPublisher($gamesCompanyId, false);
        }

        $bindings['PartnerGameList'] = $gamesList;
        $bindings['jsInitialSort'] = "[ 1, 'desc']";

        $bindings['TopTitle'] = 'Games list';
        $bindings['PageTitle'] = 'Games list';

        return view('user.games-list.list', $bindings);
    }
}
