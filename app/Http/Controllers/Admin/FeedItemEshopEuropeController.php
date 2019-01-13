<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class FeedItemEshopEuropeController extends Controller
{
    public function showList($report = null)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $eshopGameService = $serviceContainer->getEshopEuropeGameService();

        $jsInitialSort = "[ 3, 'desc']";
        if ($report == null) {
            $bindings['ActiveNav'] = 'all';
            $feedItems = $eshopGameService->getAll();
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'with-link':
                    $feedItems = $eshopGameService->getAllWithLink();
                    break;
                case 'no-link':
                    $feedItems = $eshopGameService->getAllWithoutLink();
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['TopTitle'] = 'Admin - Feed items - eShop: Europe';
        $bindings['PageTitle'] = 'Feed items - eShop: Europe';
        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.feed-items.eshop.europe.list', $bindings);
    }

    public function view($itemId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $eshopGameService = $serviceContainer->getEshopEuropeGameService();
        $gameData = $eshopGameService->getByFsId($itemId);
        if (!$gameData) abort(404);

        $bindings['GameData'] = $gameData->toArray();

        $bindings['TopTitle'] = 'Admin - Feed items - eShop: Europe - '.$gameData->title;
        $bindings['PageTitle'] = $gameData->title.' - Feed items - eShop: Europe';

        return view('admin.feed-items.eshop.europe.view', $bindings);
    }
}
