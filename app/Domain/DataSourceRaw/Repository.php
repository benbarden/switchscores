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

    public function getByIds(array $ids)
    {
        return DataSourceRaw::whereIn('id', $ids)->get();
    }

    public function markDelistedBeforeDate($sourceId, $date)
    {
        return DataSourceRaw::where('source_id', $sourceId)
            ->where(function($q) use ($date) {
                $q->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $date);
            })
            ->where('is_delisted', 0)
            ->get();
    }

    public function deleteBySourceId($sourceId)
    {
        DataSourceRaw::where('source_id', $sourceId)->delete();
    }
}