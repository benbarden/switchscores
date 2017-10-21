<?php


namespace App\Services;

use App\ChartsDate;


class ChartsDateService
{
    private function getCountryField($country)
    {
        switch ($country) {
            case 'eu':
            case 'EU':
                $countryField = 'stats_europe';
                break;
            case 'us':
            case 'US':
                $countryField = 'stats_us';
                break;
            default:
                throw new \Exception('Unknown country code: '.$country);
        }

        return $countryField;
    }

    public function getDateList($country, $limit = null)
    {
        $countryField = $this->getCountryField($country);

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

    public function getNext($country, $date)
    {
        $countryField = $this->getCountryField($country);
        $chartDate = ChartsDate::where($countryField, 'Y')
            ->where('chart_date', '>', $date)
            ->orderBy('chart_date', 'ASC')
            ->first();
        return $chartDate;
    }

    public function getPrevious($country, $date)
    {
        $countryField = $this->getCountryField($country);
        $chartDate = ChartsDate::where($countryField, 'Y')
            ->where('chart_date', '<', $date)
            ->orderBy('chart_date', 'DESC')
            ->first();
        return $chartDate;
    }
}