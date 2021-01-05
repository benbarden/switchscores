<?php

namespace App\Http\Controllers\GamesCompanies;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class IndexController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function show()
    {
        $bindings = [];

        $bindings['ReviewSitesWithContactDetails'] = $this->getServicePartner()->getActiveReviewSitesWithContactDetails();

        $pageTitle = 'Games company dashboard';
        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        return view('games-companies.index', $bindings);
    }
}
