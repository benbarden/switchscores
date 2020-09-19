<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class ToolsController extends Controller
{
    use SwitchServices;

    public function updateGameCalendarStats()
    {
        $pageTitle = 'Update Game Calendar Stats';
        $topTitle = $pageTitle.' - Tools - Games - Staff';

        $bindings = [];
        $bindings['TopTitle'] = $topTitle;
        $bindings['PageTitle'] = $pageTitle;

        if (request()->post()) {
            \Artisan::call('UpdateGameCalendarStats', []);
            return view('staff.games.tools.updateGameCalendarStats.process', $bindings);
        } else {
            return view('staff.games.tools.updateGameCalendarStats.landing', $bindings);
        }
    }
}
