<?php

namespace App\Http\Controllers\Staff\Eshop;

use App\EshopEuropeAlert;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\Services\Eshop\Europe\ReportData;
use App\Services\Eshop\Europe\FieldMapper;

class AlertsController extends Controller
{
    use SwitchServices;

    public function showErrors()
    {
        $serviceEshopEuropeAlert = $this->getServiceEshopEuropeAlert();

        $bindings = [];

        $bindings['TopTitle'] = 'eShop alerts: Errors';
        $bindings['PageTitle'] = 'eShop alerts: Errors';

        $bindings['ItemList'] = $serviceEshopEuropeAlert->getByType(EshopEuropeAlert::TYPE_ERROR);
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('staff.eshop.alertsList', $bindings);
    }

    public function showWarnings()
    {
        $serviceEshopEuropeAlert = $this->getServiceEshopEuropeAlert();

        $bindings = [];

        $bindings['TopTitle'] = 'eShop alerts: Warnings';
        $bindings['PageTitle'] = 'eShop alerts: Warnings';

        $bindings['ItemList'] = $serviceEshopEuropeAlert->getByType(EshopEuropeAlert::TYPE_WARNING);
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('staff.eshop.alertsList', $bindings);
    }

}
