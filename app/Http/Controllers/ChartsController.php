<?php

namespace App\Http\Controllers;

class ChartsController extends BaseController
{
    public function landing()
    {
        $bindings = array();

        $chartsDateService = resolve('Services\ChartsDateService');
        $chartDatesEu = $chartsDateService->getDateList('eu');
        $chartDatesUs = $chartsDateService->getDateList('us');

        $bindings['TopTitle'] = 'Charts';
        $bindings['PageTitle'] = 'Charts';
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

        $chartsRankingEuService = resolve('Services\ChartsRankingService');
        $chartsRankingUsService = resolve('Services\ChartsRankingUsService');

        if ($region == 'US') {
            $chartsRankingService = $chartsRankingUsService;
            $title = 'Charts - US';
            $regionText = 'US';
            $regionRoute = 'charts.us.date';
        } else {
            $chartsRankingService = $chartsRankingEuService;
            $title = 'Charts - Europe';
            $regionText = 'European';
            $regionPath = 'eu';
            $regionRoute = 'charts.date';
        }

        $gamesList = $chartsRankingService->getByDate($date);

        if (count($gamesList) == 0) {
            abort(404);
        }

        $chartDate = new \DateTime($date);
        $chartDateDesc = $chartDate->format('jS M Y');

        $pageTitle = $regionText.' eShop Charts: '.$chartDateDesc;

        $bindings['TopTitle'] = $title;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['RegionText'] = $regionText;
        $bindings['ChartDate'] = $date;
        $bindings['GamesList'] = $gamesList;
        $bindings['RegionRoute'] = $regionRoute; // required for prev/next links

        // Next/Previous links
        $chartsDateService = resolve('Services\ChartsDateService');
        $dateNext = $chartsDateService->getNext($region, $date);
        $datePrev = $chartsDateService->getPrevious($region, $date);
        if ($dateNext) {
            $bindings['ChartDateNext'] = $dateNext;
        }
        if ($datePrev) {
            $bindings['ChartDatePrev'] = $datePrev;
        }

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
        $bindings['PageTitle'] = 'Most appearances in the eShop Top 15';

        return view('charts.mostAppearances', $bindings);
    }

    public function gamesAtPositionLanding()
    {
        $bindings = array();
        $bindings['TopTitle'] = 'Charts - Games at position';
        $bindings['PageTitle'] = 'Games at position X in the eShop Top 15';

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
        $bindings['PageTitle'] = 'Games at No '.$position.' in the eShop Top 15';
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
