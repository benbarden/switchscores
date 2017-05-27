<?php

namespace App\Http\Controllers;

class ChartsController extends BaseController
{
    public function show($date = null)
    {
        if (!$date) {
            $date = \DB::table('charts_rankings')->max('chart_date');
        }

        $bindings = array();

        $gamesList = \App\ChartsRanking::where('chart_date', $date)->orderBy('position', 'asc')->get();

        $bindings['TopTitle'] = 'Charts | Nintendo Switch charts and stats';
        $bindings['ChartDate'] = $date;
        $bindings['GamesList'] = $gamesList;

        return view('charts.topFifteen', $bindings);
    }
}
