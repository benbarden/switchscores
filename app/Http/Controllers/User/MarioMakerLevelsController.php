<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;
use App\MarioMakerLevel;
use Auth;

class MarioMakerLevelsController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'level_code' => 'required|max:11',
        'game_style_id' => 'required',
        'title' => 'required|max:100',
        'description' => 'max:500'
    ];

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userId = Auth::id();

        $request = request();

        $serviceMarioMakerLevel = $serviceContainer->getMarioMakerLevelService();

        $levelDesc = $request->description;
        $levelDesc = strip_tags($levelDesc);
        $levelDesc = nl2br($levelDesc);

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $levelCode = strtoupper($request->level_code);
            $pendingStatus = MarioMakerLevel::STATUS_PENDING;

            $marioMakerLevel = $serviceMarioMakerLevel->create(
                $userId, $levelCode, $request->game_style_id,
                $request->title, $levelDesc, $pendingStatus
            );

            return redirect(route('user.mario-maker-levels.list').'?msg=success');

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Add Mario Maker level';
        $bindings['PageTitle'] = 'Add Mario Maker level';
        $bindings['FormMode'] = 'add';

        $bindings['GameStyleList'] = $serviceMarioMakerLevel->getGameStyleList();

        return view('user.mario-maker-levels.add', $bindings);
    }

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $urlMsg = \Request::get('msg');

        $serviceMarioMakerLevel = $serviceContainer->getMarioMakerLevelService();

        $userId = Auth::id();

        $bindings = [];

        $bindings['TopTitle'] = 'Mario Maker levels';
        $bindings['PageTitle'] = 'Mario Maker levels';

        if ($urlMsg) {
            $bindings['MsgSuccess'] = true;
        }

        $bindings['LevelList'] = $serviceMarioMakerLevel->getByUserId($userId);

        return view('user.mario-maker-levels.list', $bindings);
    }
}
