<?php

namespace App\Http\Controllers;

use App\ChartsRankingGlobal;

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

    public function show($countryCode, $date)
    {
        $bindings = array();

        $chartsRankingGlobalService = resolve('Services\ChartsRankingGlobalService');
        /* @var $chartsRankingGlobalService \App\Services\ChartsRankingGlobalService */

        switch ($countryCode) {
            case ChartsRankingGlobal::COUNTRY_US;
                $title = 'Charts - US';
                $regionText = 'US';
                break;
            case ChartsRankingGlobal::COUNTRY_EU;
                $title = 'Charts - Europe';
                $regionText = 'European';
                break;
            default:
                abort(404);
                break;
        }

        $gamesList = $chartsRankingGlobalService->getByCountryAndDate($countryCode, $date);

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
        $bindings['CountryCode'] = $countryCode;

        // Next/Previous links
        $chartsDateService = resolve('Services\ChartsDateService');
        /* @var $chartsDateService \App\Services\ChartsDateService */

        $dateNext = $chartsDateService->getNext($countryCode, $date);
        $datePrev = $chartsDateService->getPrevious($countryCode, $date);
        if ($dateNext) {
            $bindings['ChartDateNext'] = $dateNext;
        }
        if ($datePrev) {
            $bindings['ChartDatePrev'] = $datePrev;
        }

        return view('charts.topFifteen', $bindings);
    }

    public function mostAppearances()
    {
        $bindings = array();

        $bindings['GamesListEu'] = \DB::select("
            SELECT cr.game_id, g.title, g.link_title, count(*) AS count
            FROM charts_rankings_global cr
            JOIN games g ON cr.game_id = g.id
            WHERE cr.country_code = 'eu'
            GROUP BY cr.game_id ORDER BY count(*) DESC
        ");

        $bindings['GamesListUs'] = \DB::select("
            SELECT cr.game_id, g.title, g.link_title, count(*) AS count
            FROM charts_rankings_global cr
            JOIN games g ON cr.game_id = g.id
            WHERE cr.country_code = 'us'
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
            FROM charts_rankings_global cr
            JOIN games g ON cr.game_id = g.id
            WHERE cr.country_code = 'eu'
            AND cr.position = ?
            GROUP BY cr.game_id ORDER BY count(*) DESC
        ", array($position));

        return view('charts.gamesAtPosition', $bindings);
    }

    public function redirectEu($date)
    {
        $redirUrl = sprintf('/charts/eu/%s', $date);
        return redirect($redirUrl, 301);
    }

    public function redirectUs($date)
    {
        $redirUrl = sprintf('/charts/us/%s', $date);
        return redirect($redirUrl, 301);
    }
}
