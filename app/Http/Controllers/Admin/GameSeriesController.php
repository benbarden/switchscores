<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use Auth;

class GameSeriesController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Game series';
        $bindings['PageTitle'] = 'Game series';

        $serviceGameSeries = $serviceContainer->getGameSeriesService();
        $bindings['GameSeriesList'] = $serviceGameSeries->getAll();

        return view('admin.game-series.list', $bindings);
    }

    public function addGameSeries()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGameSeries = $serviceContainer->getGameSeriesService();
        $serviceUser = $serviceContainer->getUserService();
        $serviceUrl = $serviceContainer->getUrlService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $seriesName = $request->seriesName;
        if (!$seriesName) {
            return response()->json(['error' => 'Missing data: seriesName'], 400);
        }

        $existingRecord = $serviceGameSeries->getByName($seriesName);
        if ($existingRecord) {
            return response()->json(['error' => 'Game series already exists!'], 400);
        }

        $linkTitle = $serviceUrl->generateLinkText($seriesName);

        $serviceGameSeries->create($seriesName, $linkTitle);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}