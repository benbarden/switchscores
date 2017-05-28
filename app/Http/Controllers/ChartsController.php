<?php

namespace App\Http\Controllers;

class ChartsController extends BaseController
{
    public function landing()
    {
        $bindings = array();

        $chartDates = \DB::table('charts_rankings')->groupBy('chart_date')->orderBy('chart_date', 'DESC')->get();
        $bindings['TopTitle'] = 'Charts';
        $bindings['ChartDates'] = $chartDates;

        return view('charts.landing', $bindings);
    }

    public function show($date = null)
    {
        if (!$date) {
            abort(404);
        }

        $bindings = array();

        $gamesList = \App\ChartsRanking::where('chart_date', $date)->orderBy('position', 'asc')->get();

        if (count($gamesList) == 0) {
            abort(404);
        }

        $bindings['TopTitle'] = 'Charts';
        $bindings['ChartDate'] = $date;
        $bindings['GamesList'] = $gamesList;

        return view('charts.topFifteen', $bindings);
    }

    public function mostAppearances()
    {
        $bindings = array();

        $bindings['GamesList'] = \DB::select("
            SELECT cr.game_id, g.title, g.link_title, count(*) AS count
            FROM charts_rankings cr
            JOIN games g ON cr.game_id = g.id
            GROUP BY cr.game_id ORDER BY count(*) DESC
        ");

        $bindings['TopTitle'] = 'Charts - Most appearances';

        return view('charts.mostAppearances', $bindings);
    }

    public function gamesAtPositionLanding()
    {
        $bindings = array();
        $bindings['TopTitle'] = 'Charts - Games at position';

        $fifteenList = array();
        for ($i=1; $i<=15; $i++) {
            $fifteenList[] = $i;
        }

        $bindings['PositionList'] = $fifteenList;

        return view('charts.gamesAtPositionLanding', $bindings);
    }

    public function gamesAtPosition($position)
    {
        $bindings = array();
        $bindings['TopTitle'] = 'Charts - Games at position '.$position;
        $bindings['PositionNo'] = $position;

        $bindings['GamesList'] = \DB::select("
            SELECT cr.game_id, g.title, g.link_title, count(*) AS count
            FROM charts_rankings cr
            JOIN games g ON cr.game_id = g.id
            WHERE cr.position = ?
            GROUP BY cr.game_id ORDER BY count(*) DESC
        ", array($position));

        return view('charts.gamesAtPosition', $bindings);
    }
}
