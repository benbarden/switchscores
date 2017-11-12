<?php

namespace App\Http\Controllers\Admin;

class ToolsController extends \App\Http\Controllers\BaseController
{
    public function landing()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools';
        $bindings['PanelTitle'] = 'Tools';

        return view('admin.tools.landing', $bindings);
    }

    public function updateGameRanksLanding()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Ranks';
        $bindings['PanelTitle'] = 'Tools - Update Game Ranks';

        return view('admin.tools.updateGameRanks.landing', $bindings);
    }

    public function updateGameRanksProcess()
    {
        \Artisan::call('UpdateGameRanks');

        $bindings = array();

        $bindings['TopTitle'] = 'Tools - Update Game Ranks';
        $bindings['PanelTitle'] = 'Tools - Update Game Ranks';

        return view('admin.tools.updateGameRanks.process', $bindings);
    }
}
