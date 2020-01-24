<?php

namespace App\Http\Controllers\Staff\Eshop;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\Services\Eshop\Europe\ReportData;
use App\Services\Eshop\Europe\FieldMapper;

class ReportController extends Controller
{
    use SwitchServices;

    public function show($reportName)
    {
        $serviceFieldMapper = new FieldMapper();
        $serviceFieldMapper->setField($reportName);
        if (!$serviceFieldMapper->fieldExists()) abort(404);

        $fieldName = $serviceFieldMapper->getDbFieldName();
        $reportTitle = $serviceFieldMapper->getReportTitle();

        $serviceReportData = new ReportData();

        if ($serviceFieldMapper->isBoolean()) {
            $reportData = $serviceReportData->getGenericBooleanReport($fieldName);
        } else {
            abort(400);
        }

        $pageTitle = 'eShop report: '.$reportTitle;

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['FieldName'] = $fieldName;
        $bindings['ReportData'] = $reportData;
        $bindings['ReportName'] = $reportName;

        return view('staff.eshop.report', $bindings);
    }

    public function gameList($reportName, $filterValue)
    {
        $serviceFieldMapper = new FieldMapper();
        $serviceFieldMapper->setField($reportName);
        if (!$serviceFieldMapper->fieldExists()) abort(404);

        $fieldName = $serviceFieldMapper->getDbFieldName();
        $reportTitle = $serviceFieldMapper->getReportTitle();

        $serviceReportData = new ReportData();

        if ($serviceFieldMapper->isBoolean()) {
            $reportData = $serviceReportData->getReportFieldData($fieldName, $filterValue);
        } else {
            abort(400);
        }

        $pageTitle = 'Game list for eShop report: '.$reportTitle;

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle.' - Staff';
        $bindings['PageTitle'] = $pageTitle;

        $bindings['FieldName'] = $fieldName;
        $bindings['ReportData'] = $reportData;
        $bindings['ReportName'] = $reportName;
        $bindings['ReportTitle'] = $reportTitle;

        $bindings['FilterValue'] = $filterValue;

        return view('staff.eshop.reportGameList', $bindings);
    }
}
