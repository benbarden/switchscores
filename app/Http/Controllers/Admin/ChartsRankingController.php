<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\ChartsRankingGlobal;

class ChartsRankingController extends \App\Http\Controllers\BaseController
{
    const MAX_RANK_COUNT = 15;

    public function showList($countryCode, $date)
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Charts - Rankings';

        $chartsRankingGlobalService = resolve('Services\ChartsRankingGlobalService');
        /* @var $chartsRankingGlobalService \App\Services\ChartsRankingGlobalService */

        switch ($countryCode) {
            case ChartsRankingGlobal::COUNTRY_EU:
                $countryDesc = "Europe";
                break;
            case ChartsRankingGlobal::COUNTRY_US:
                $countryDesc = "US";
                break;
            default:
                abort(404);
        }

        $chartRankings = $chartsRankingGlobalService->getByCountryAndDate($countryCode, $date);

        $bindings['PanelTitle'] = 'Charts: Rankings for '.$date.' - '.$countryDesc;

        $bindings['CountryCode'] = $countryCode;
        $bindings['CountryDesc'] = $countryDesc;
        $bindings['ChartDate'] = $date;
        $bindings['ChartRankings'] = $chartRankings;

        return view('admin.charts.ranking.list', $bindings);
    }

    public function add($countryCode, $date)
    {
        $request = request();
        if ($request->isMethod('post')) {
            $uniqueCount = count(array_unique($request->ranking_games));
            if ($uniqueCount != self::MAX_RANK_COUNT) {
                //return redirect('admin.charts.ranking-eu.add')->withErrors($validator);
            }

            $position = 1;
            foreach ($request->ranking_games as $key => $val) {
                $rankData = [
                    'country_code' => $countryCode,
                    'chart_date' => $date,
                    'position' => $position,
                    'game_id' => $val
                ];
                ChartsRankingGlobal::create($rankData);
                $position++;
            }

            // Set availability to Y for this country
            $chartsDate = \App\ChartsDate::where('chart_date', $date)->first();
            if ($chartsDate) {
                switch ($countryCode) {
                    case ChartsRankingGlobal::COUNTRY_EU:
                        $chartsDate->stats_europe = 'Y';
                        break;
                    case ChartsRankingGlobal::COUNTRY_US:
                        $chartsDate->stats_us = 'Y';
                        break;
                    default:
                        abort(404);
                }
                $chartsDate->save();
            }

            return redirect(route('admin.charts.ranking.list', ['country' => $countryCode, 'date' => $date]));
        }

        $bindings = array();

        $bindings['ChartDate'] = $date;

        $bindings['CountryCode'] = $countryCode;

        switch ($countryCode) {
            case ChartsRankingGlobal::COUNTRY_EU:
                $countryDesc = "Europe";
                break;
            case ChartsRankingGlobal::COUNTRY_US:
                // For US charts, get all games as some upcoming games may be out in the US
                $countryDesc = "US";
                break;
            default:
                abort(404);
        }

        // Now that it's possible for preorders to show in the charts,
        // we have to show all games in the dropdowns
        $gamesList = \App\Game::orderBy('title', 'asc')->get();

        $bindings['TopTitle'] = 'Admin - Charts - Add ranking ('.$countryDesc.')';
        $bindings['PanelTitle'] = 'Add rankings - '.$date.' - '.$countryDesc;

        $bindings['GamesList'] = $gamesList;

        $bindings['MaxRankCount'] = self::MAX_RANK_COUNT;

        return view('admin.charts.ranking.add', $bindings);
    }
}
