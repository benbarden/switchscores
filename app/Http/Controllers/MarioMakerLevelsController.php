<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class MarioMakerLevelsController extends Controller
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $serviceMarioMakerLevels = $serviceContainer->getMarioMakerLevelService();

        $bindings['LevelList'] = $serviceMarioMakerLevels->getApproved();

        $bindings['TopTitle'] = 'Super Mario Maker 2 levels';
        $bindings['PageTitle'] = 'Super Mario Maker 2 levels';

        return view('mario-maker.landing', $bindings);
    }
}
