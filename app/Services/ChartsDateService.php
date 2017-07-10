<?php


namespace App\Services;

use App\ChartsDate;


class ChartsDateService
{
    public function getDateList($country)
    {
        switch ($country) {
            case 'eu':
                $countryField = 'stats_europe';
                break;
            case 'us':
                $countryField = 'stats_us';
                break;
            default:
                throw new \Exception('Unknown country code: '.$country);
        }

        $dateList = ChartsDate::where($countryField, 'Y')->orderBy('chart_date', 'DESC')->get();

        return $dateList;
    }
}