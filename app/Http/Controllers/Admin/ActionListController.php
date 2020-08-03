<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class ActionListController extends Controller
{
    use SwitchServices;

    public function noPrice()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['TopTitle'] = 'Games without prices - Action lists - Admin';
        $bindings['PageTitle'] = 'Games without prices';

        $bindings['GameList'] = $serviceGame->getWithoutPrices();
        $bindings['jsInitialSort'] = "[ 4, 'desc']";

        return view('admin.action-lists.game-prices.list', $bindings);
    }
}