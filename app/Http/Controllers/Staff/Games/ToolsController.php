<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class ToolsController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function updateGameCalendarStats()
    {
        $bindings = $this->getBindingsGamesSubpage('Update Game Calendar Stats');

        if (request()->post()) {
            \Artisan::call('UpdateGameCalendarStats', []);
            return view('staff.games.tools.updateGameCalendarStats.process', $bindings);
        } else {
            return view('staff.games.tools.updateGameCalendarStats.landing', $bindings);
        }
    }
}
