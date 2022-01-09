<?php


namespace App\Services;

use App\Models\DataSourceRaw;

class DataSourceRawService
{
    public function find($itemId)
    {
        return DataSourceRaw::find($itemId);
    }

    public function getBySourceId($sourceId)
    {
        return DataSourceRaw::where('source_id', $sourceId)->orderBy('id', 'asc')->get();
    }

    public function deleteBySourceId($sourceId)
    {
        DataSourceRaw::where('source_id', $sourceId)->delete();
    }
}