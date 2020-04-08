<?php

namespace App\Http\Controllers\Staff\Eshop;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\EshopEuropeAlert;

use App\Services\Eshop\Europe\FieldMapper;

class DashboardController extends Controller
{
    use SwitchServices;

    public function show()
    {
        $pageTitle = 'eShop dashboard';

        $serviceEshopEurope = $this->getServiceEshopEuropeGame();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        // Action lists
        $bindings['NoPriceCount'] = $this->getServiceGame()->countWithoutPrices();

        // Stats
        $ignoreFsIdList = $this->getServiceEshopEuropeIgnore()->getIgnoredFsIdList();
        $bindings['EshopEuropeTotalCount'] = $serviceEshopEurope->getTotalCount();
        $bindings['EshopEuropeLinkedCount'] = $serviceEshopEurope->getAllWithLink($ignoreFsIdList, null, true);
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink($ignoreFsIdList, null, true);
        $bindings['NoEshopEuropeLinkCount'] = $this->getServiceGameFilterList()->getGamesWithoutEshopEuropeFsId()->count();

        return view('staff.eshop.dashboard', $bindings);
    }
}
