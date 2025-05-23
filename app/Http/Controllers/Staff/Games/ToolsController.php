<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

class ToolsController extends Controller
{
    public function updateGameCalendarStats()
    {
        $pageTitle = 'Update Game Calendar Stats';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        if (request()->post()) {
            \Artisan::call('UpdateGameCalendarStats', []);
            return view('staff.games.tools.updateGameCalendarStats.process', $bindings);
        } else {
            return view('staff.games.tools.updateGameCalendarStats.landing', $bindings);
        }
    }
}
