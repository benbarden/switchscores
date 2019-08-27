<?php

namespace App\Http\Controllers\User;

use App\Traits\AuthUser;
use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;


class UserProfileController extends Controller
{
    use AuthUser;

    public function updateRegion()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userService = $serviceContainer->getUserService();

        $request = request();

        $regionCode = $request->regionCode;
        if (!$regionCode) {
            return response()->json(['error' => 'Missing data: regionCode'], 400);
        }
        if (!in_array($regionCode, ['eu', 'us', 'jp'])) {
            return response()->json(['error' => 'Invalid data for regionCode'], 400);
        }

        $user = $this->getValidUser($userService);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        if ($user->region == $regionCode) {
            // Nothing to do!
        } else {
            $user->region = $regionCode;
            $user->save();
        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
