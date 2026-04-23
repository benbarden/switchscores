<?php

namespace App\Domain\DataSourceRaw;

use App\Models\DataSourceRaw;

class Repository
{
    public function find($itemId)
    {
        return DataSourceRaw::find($itemId);
    }

    public function getBySourceId($sourceId)
    {
        return DataSourceRaw::where('source_id', $sourceId)->orderBy('id', 'asc')->get();
    }

    public function searchBySourceIdAndTitle($sourceId, $title)
    {
        return DataSourceRaw::where('source_id', $sourceId)
            ->where('title', 'like', '%'.$title.'%')
            ->orderBy('title', 'asc')
            ->get();
    }

    public function findBySourceIdAndLinkId($sourceId, $linkId)
    {
        return DataSourceRaw::where('source_id', $sourceId)->where('link_id', $linkId)->first();
    }

    public function deleteBySourceId($sourceId)
    {
        DataSourceRaw::where('source_id', $sourceId)->delete();
    }
}