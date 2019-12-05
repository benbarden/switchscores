<?php

namespace App\Http\Controllers\Staff\Eshop;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use App\EshopEuropeAlert;

use App\Services\Eshop\Europe\FieldMapper;

class DashboardController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show()
    {
        $pageTitle = 'eShop dashboard';

        $serviceGame = $this->getServiceGame();
        $serviceEshopEurope = $this->getServiceEshopEuropeGame();
        $serviceEshopEuropeAlert = $this->getServiceEshopEuropeAlert();

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Admin';
        $bindings['PageTitle'] = $pageTitle;

        // Action lists
        $bindings['NoPriceCount'] = $serviceGame->countWithoutPrices();
        $bindings['EshopAlertErrorCount'] = $serviceEshopEuropeAlert->countByType(EshopEuropeAlert::TYPE_ERROR);
        $bindings['EshopAlertWarningCount'] = $serviceEshopEuropeAlert->countByType(EshopEuropeAlert::TYPE_WARNING);

        // Stats
        $bindings['EshopEuropeTotalCount'] = $serviceEshopEurope->getTotalCount();
        $bindings['EshopEuropeLinkedCount'] = $serviceEshopEurope->getAllWithLink(null, true);
        $bindings['EshopEuropeUnlinkedCount'] = $serviceEshopEurope->getAllWithoutLink(null, true);
        $bindings['NoEshopEuropeLinkCount'] = $this->getServiceGameFilterList()->getGamesWithoutEshopEuropeFsId()->count();

        // Report list
        $serviceFieldMapper = new FieldMapper();
        $reportList = $serviceFieldMapper->getBooleanReportList();
        $bindings['ReportList'] = $reportList;

        return view('staff.eshop.dashboard', $bindings);
    }
}
