<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use Auth;

class GamePrimaryTypesController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Game primary types';
        $bindings['PageTitle'] = 'Game primary types';

        $servicePrimaryTypes = $serviceContainer->getGamePrimaryTypeService();
        $bindings['PrimaryTypeList'] = $servicePrimaryTypes->getAll();

        return view('admin.game-primary-types.list', $bindings);
    }

    public function addPrimaryType()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePrimaryTypes = $serviceContainer->getGamePrimaryTypeService();
        $serviceUser = $serviceContainer->getUserService();
        $serviceUrl = $serviceContainer->getUrlService();

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