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

        $feedItems = $eshopGameService->getAll();
        $jsInitialSort = "[ 0, 'desc']";

        $bindings['TopTitle'] = 'Admin - Feed items - eShop: Europe';
        $bindings['PanelTitle'] = 'Feed items - eShop: Europe';
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
        $gameData = $eshopGameService->find($itemId);
        if (!$gameData) abort(404);

        $bindings['GameData'] = $gameData->toArray();

        $bindings['TopTitle'] = 'Admin - Feed items - eShop: Europe - '.$gameData->title;
        $bindings['PanelTitle'] = $gameData->title.' - Feed items - eShop: Europe';

        return view('admin.feed-items.eshop.europe.view', $bindings);
    }
}
