<?php


namespace App\Services;

use App\DataSourceParsed;

class DataSourceParsedService
{
    public function deleteBySourceId($sourceId)
    {
        DataSourceParsed::where('source_id', $sourceId)->delete();
    }
}