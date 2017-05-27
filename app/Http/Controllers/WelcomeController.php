<?php

namespace App\Http\Controllers;

class WelcomeController extends BaseController
{
    public function show()
    {
        $bindings = array();

        $chartDates = \DB::table('charts_rankings')->groupBy('chart_date')->orderBy('chart_date', 'DESC')->get();

        $bindings['TopTitle'] = 'Welcome | Nintendo Switch charts and stats';
        $bindings['ChartDates'] = $chartDates;

        return view('welcome', $bindings);
    }
}
