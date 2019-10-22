<?php

namespace App\Http\Controllers\Staff\Eshop;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class DashboardController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $pageTitle = 'eShop dashboard';

        $serviceGame = $this->getServiceGame();
        $serviceEshopEurope = $this->getServiceEshopEuropeGame();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        // Action lists
        $bindings['NoPriceCount'] = $serviceGame->countWithoutPrices();

        // Stats
        $bindings['EshopEuropeTotalCount'] = $serviceEshopEurope->getTotalCount();
        $bindings['EshopEuropeLinkedCount'] = $serviceEshopEurope->getAllWithLink(null, true);
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);
        $bindings['NoEshopEuropeLinkCount'] = $this->getServiceGameFilterList()->getGamesWithoutEshopEuropeFsId()->count();

        return view('staff.eshop.dashboard', $bindings);
    }
}
