<?php

namespace App\Domain\NewsDbUpdate;

use App\Models\NewsDbUpdate;

class Repository
{
    public function create($year, $week)
    {
        $newsDbUpdate = new NewsDbUpdate();
        $newsDbUpdate->news_db_year = $year;
        $newsDbUpdate->news_db_week = $week;
        $newsDbUpdate->save();
        return $newsDbUpdate;
    }

    public function update(NewsDbUpdate $newsDbUpdate, $gameCountStandard, $gameCountLowQuality)
    {
        $data = [
            'game_count_standard' => $gameCountStandard,
            'game_count_low_quality' => $gameCountLowQuality,
        ];
        $newsDbUpdate->fill($data)->save();
    }

    public function getAllByYear($year, $descOrder = false)
    {
        $newsDbList = NewsDbUpdate::where('news_db_year', $year);
        if ($descOrder) {
            $newsDbList = $newsDbList->orderBy('news_db_week', 'desc');
        } else {
            $newsDbList = $newsDbList->orderBy('news_db_week');
        }

        return $newsDbList->get();
    }

    public function get($year, $week)
    {
        if ($week < 10) {
            $week = str_pad($week, 2, '0', STR_PAD_LEFT);
        }
        return NewsDbUpdate::where('news_db_year', $year)->where('news_db_week', $week)->first();
    }
}