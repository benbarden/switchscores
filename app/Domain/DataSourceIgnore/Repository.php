<?php

namespace App\Domain\DataSourceIgnore;

use App\Models\DataSource;
use App\Models\DataSourceIgnore;

class Repository
{
    public function getAllBySource($sourceId)
    {
        return DataSourceIgnore::where('source_id', $sourceId)->orderBy('id', 'asc')->get();
    }

    public function find($id)
    {
        return DataSourceIgnore::find($id);
    }

    public function getBySourceAndLinkId($sourceId, $linkId)
    {
        return DataSourceIgnore::where('source_id', $sourceId)->where('link_id', $linkId)->get();
    }

    public function getNintendoCoUkLinkIdList()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return DataSourceIgnore::where('source_id', $sourceId)->orderBy('link_id', 'asc')->pluck('link_id');
    }

    public function addLinkId($sourceId, $linkId)
    {
        return DataSourceIgnore::create([
            'source_id' => $sourceId,
            'link_id' => $linkId,
        ]);
    }

    public function deleteByLinkId($sourceId, $linkId)
    {
        DataSourceIgnore::where('source_id', $sourceId)->where('link_id', $linkId)->delete();
    }
}