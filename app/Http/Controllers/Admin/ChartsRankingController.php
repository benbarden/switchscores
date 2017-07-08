<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class ChartsRankingController extends \App\Http\Controllers\BaseController
{
    const MAX_RANK_COUNT = 15;

    const COUNTRY_EU = 'eu';
    const COUNTRY_US = 'us';

    public function showList($country, $date)
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Charts - Rankings';

        switch ($country) {
            case self::COUNTRY_EU:
                $chartRankings = \App\ChartsRanking::where('chart_date', $date)->orderBy('position', 'asc')->get();
                $countryDesc = "Europe";
                break;
            case self::COUNTRY_US:
                $chartRankings = \App\ChartsRankingUs::where('chart_date', $date)->orderBy('position', 'asc')->get();
                $countryDesc = "US";
                break;
            default:
                abort(404);
        }

        $bindings['CountryCode'] = $country;
        $bindings['CountryDesc'] = $countryDesc;
        $bindings['ChartDate'] = $date;
        $bindings['ChartRankings'] = $chartRankings;

        return view('admin.charts.ranking.list', $bindings);
    }

    public function add($country, $date)
    {
        $request = request();
        if ($request->isMethod('post')) {
            $uniqueCount = count(array_unique($request->ranking_games));
            if ($uniqueCount != self::MAX_RANK_COUNT) {
                //return redirect('admin.charts.ranking-eu.add')->withErrors($validator);
            }
            //exit(var_export($request->ranking_games, true));
            $position = 1;
            foreach ($request->ranking_games as $key => $val) {
                $rankData = [
                    'chart_date' => $date,
                    'position' => $position,
                    'game_id' => $val
                ];
                switch ($country) {
                    case self::COUNTRY_EU:
                        \App\ChartsRanking::create($rankData);
                        break;
                    case self::COUNTRY_US:
                        \App\ChartsRankingUs::create($rankData);
                        break;
                    default:
                        abort(404);
                }
                $position++;
            }

            // Set availability to Y for this country
            $chartsDate = \App\ChartsDate::where('chart_date', $date)->first();
            if ($chartsDate) {
                switch ($country) {
                    case self::COUNTRY_EU:
                        $chartsDate->stats_europe = 'Y';
                        break;
                    case self::COUNTRY_US:
                        $chartsDate->stats_us = 'Y';
                        break;
                    default:
                        abort(404);
                }
                $chartsDate->save();
            }

            return redirect(route('admin.charts.ranking.list', ['country' => $country, 'date' => $date]));
        }
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Charts - Add ranking (Europe)';
        $bindings['ChartDate'] = $date;

        $bindings['CountryCode'] = $country;

        switch ($country) {
            case self::COUNTRY_EU:
                $gamesList = \App\Game::where('upcoming', 0)->orderBy('title', 'asc')->get();
                $countryDesc = "Europe";
                break;
            case self::COUNTRY_US:
                // For US charts, get all games as some upcoming games may be out in the US
                $gamesList = \App\Game::orderBy('title', 'asc')->get();
                $countryDesc = "US";
                break;
            default:
                abort(404);
        }

        $bindings['GamesList'] = $gamesList;

        $bindings['MaxRankCount'] = self::MAX_RANK_COUNT;

        return view('admin.charts.ranking.add', $bindings);
    }
}
