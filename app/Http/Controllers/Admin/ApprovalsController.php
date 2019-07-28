<?php

namespace App\Http\Controllers\Admin;

use Auth;
use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

use App\MarioMakerLevel;

use App\UserPointTransaction;
use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;

class ApprovalsController extends Controller
{
    public function marioMakerLevels()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceMarioMakerLevel = $serviceContainer->getMarioMakerLevelService();

        $bindings = [];
        $bindings['TopTitle'] = 'Approvals - Mario Maker levels';
        $bindings['PageTitle'] = 'Approvals - Mario Maker levels';

        $bindings['LevelList'] = $serviceMarioMakerLevel->getPending();

        return view('admin.approvals.mario-maker-levels', $bindings);
    }

    public function approveMarioMakerLevel()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceMarioMakerLevel = $serviceContainer->getMarioMakerLevelService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $levelId = $request->levelId;
        if (!$levelId) {
            return response()->json(['error' => 'Missing data: levelId'], 400);
        }

        $marioMakerLevel = $serviceMarioMakerLevel->find($levelId);
        if (!$marioMakerLevel) {
            return response()->json(['error' => 'Cannot find level with id: '.$levelId], 400);
        }

        if ($marioMakerLevel->status != MarioMakerLevel::STATUS_PENDING) {
            return response()->json(['error' => 'Level status cannot be set as it is not Pending'], 400);
        }

        $serviceMarioMakerLevel->markAsApproved($marioMakerLevel);

        // Give the user some points
        $pointsToAdd = UserPointTransaction::POINTS_MARIO_MAKER_LEVEL_ADD;

        UserFactory::addToPointsBalance($user, $pointsToAdd);

        // Store the transaction
        $params = UserPointTransactionDirectorFactory::buildParams(
            $userId,
            UserPointTransaction::ACTION_MARIO_MAKER_LEVEL_ADD,
            null,
            $pointsToAdd,
            null
        );
        UserPointTransactionDirectorFactory::createNew($params);


        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function rejectMarioMakerLevel()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceMarioMakerLevel = $serviceContainer->getMarioMakerLevelService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $levelId = $request->levelId;
        if (!$levelId) {
            return response()->json(['error' => 'Missing data: levelId'], 400);
        }

        $marioMakerLevel = $serviceMarioMakerLevel->find($levelId);
        if (!$marioMakerLevel) {
            return response()->json(['error' => 'Cannot find level with id: '.$levelId], 400);
        }

        if ($marioMakerLevel->status != MarioMakerLevel::STATUS_PENDING) {
            return response()->json(['error' => 'Level status cannot be set as it is not Pending'], 400);
        }

        $serviceMarioMakerLevel->markAsRejected($marioMakerLevel);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}