<?php


namespace App\Services;

use App\ChartsDate;


class ChartsDateService
{
    public function getDateList($country, $limit = null)
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

        $dateList = ChartsDate::where($countryField, 'Y')
            ->orderBy('chart_date', 'DESC');

        if ($limit == 1) {
            $dateList = $dateList->first();
        } elseif ($limit) {
            $dateList = $dateList->limit($limit);
            $dateList = $dateList->get();
        } else {
            $dateList = $dateList->get();
        }

        return $dateList;
    }
}