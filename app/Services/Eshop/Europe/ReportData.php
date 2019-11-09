<?php

namespace App\Services\Eshop\Europe;

use App\EshopEuropeGame;
use Illuminate\Support\Facades\DB;

class ReportData
{
    public function getGenericBooleanReport($field)
    {
        return DB::select("
            SELECT $field as field_value, count(*) as field_count
            FROM eshop_europe_games
            GROUP BY $field
            ORDER BY field_count ASC
        ");
    }

    public function getReportFieldData($field, $value)
    {
        if ($value == 'no-value') {
            return EshopEuropeGame::whereNull($field)->get();
        } else {
            return EshopEuropeGame::where($field, $value)->get();
        }
    }
}