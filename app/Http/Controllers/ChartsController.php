<?php

namespace App\Http\Controllers;

class ChartsController extends BaseController
{
    public function landing()
    {
        $bindings = array();

        $chartDatesEu = \App\ChartsDate::where('stats_europe', 'Y')->orderBy('chart_date', 'DESC')->get();
        $chartDatesUs = \App\ChartsDate::where('stats_us', 'Y')->orderBy('chart_date', 'DESC')->get();

        $bindings['TopTitle'] = 'Charts';
        $bindings['ChartDatesEu'] = $chartDatesEu;
        $bindings['ChartDatesUs'] = $chartDatesUs;

        return view('charts.landing', $bindings);
    }

    private function show($date = null, $region = '')
    {
        if (!$date) {
            abort(404);
        }

        $bindings = array();

        if ($region == 'US') {
            $gamesList = \App\ChartsRankingUs::where('chart_date', $date)->orderBy('position', 'asc')->get();
            $title = 'Charts - US';
            $regionText = 'US';
        } else {
            $gamesList = \App\ChartsRanking::where('chart_date', $date)->orderBy('position', 'asc')->get();
            $title = 'Charts - Europe';
            $regionText = 'European';
        }

        if (count($gamesList) == 0) {
            abort(404);
        }

        $bindings['TopTitle'] = $title;
        $bindings['RegionText'] = $regionText;
        $bindings['ChartDate'] = $date;
        $bindings['GamesList'] = $gamesList;

        return $bindings;
    }

    public function showEu($date = null)
    {
        $bindings = $this->show($date, 'EU');
        return view('charts.topFifteen', $bindings);
    }

    public function showUs($date = null)
    {
        $bindings = $this->show($date, 'US');
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
