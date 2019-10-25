<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use Auth;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

class GamePrimaryTypesController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function showList()
    {
        $servicePrimaryTypes = $this->getServiceGamePrimaryType();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Game primary types';
        $bindings['PageTitle'] = 'Game primary types';

        $bindings['PrimaryTypeList'] = $servicePrimaryTypes->getAll();

        return view('staff.categorisation.game-primary-types.list', $bindings);
    }

    public function addPrimaryType()
    {
        $servicePrimaryTypes = $this->getServiceGamePrimaryType();
        $serviceUser = $this->getServiceUser();
        $serviceUrl = $this->getServiceUrl();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $primaryType = $request->primaryType;
        if (!$primaryType) {
            return response()->json(['error' => 'Missing data: primaryType'], 400);
        }

        $existingRecord = $servicePrimaryTypes->getByName($primaryType);
        if ($existingRecord) {
            return response()->json(['error' => 'Primary type already exists!'], 400);
        }

        $linkTitle = $serviceUrl->generateLinkText($primaryType);

        $servicePrimaryTypes->create($primaryType, $linkTitle);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}