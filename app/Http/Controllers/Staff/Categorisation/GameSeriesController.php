<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

class GameSeriesController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    public function showList()
    {
        $bindings = $this->getBindingsCategorisationSubpage('Game series');

        $bindings['GameSeriesList'] = $this->getServiceGameSeries()->getAll();

        return view('staff.categorisation.game-series.list', $bindings);
    }

    public function addGameSeries()
    {
        $serviceGameSeries = $this->getServiceGameSeries();
        $serviceUser = $this->getServiceUser();
        $serviceUrl = $this->getServiceUrl();

        $userId = $this->getAuthId();

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