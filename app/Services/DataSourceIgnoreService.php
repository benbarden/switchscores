<?php


namespace App\Services;

use App\Models\DataSource;
use App\Models\DataSourceIgnore;

class DataSourceIgnoreService
{
    public function getAllBySource($sourceId)
    {
        return DataSourceIgnore::where('source_id', $sourceId)->orderBy('id', 'asc')->get();
    }

    public function getAllNintendoCoUk()
    {
        return $this->getAllBySource(DataSource::DSID_NINTENDO_CO_UK);
    }

    public function getAllWikipedia()
    {
        return $this->getAllBySource(DataSource::DSID_WIKIPEDIA);
    }

    public function find($id)
    {
        return DataSourceIgnore::find($id);
    }

    public function getBySourceAndLinkId($sourceId, $linkId)
    {
        return DataSourceIgnore::where('source_id', $sourceId)->where('link_id', $linkId)->get();
    }

    public function getBySourceAndTitle($sourceId, $title)
    {
        return DataSourceIgnore::where('source_id', $sourceId)->where('title', $title)->get();
    }

    public function getNintendoCoUkLinkIdList()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return DataSourceIgnore::where('source_id', $sourceId)->orderBy('link_id', 'asc')->pluck('link_id');
    }

    public function getWikipediaTitleList()
    {
        $sourceId = DataSource::DSID_WIKIPEDIA;
        return DataSourceIgnore::where('source_id', $sourceId)->orderBy('title', 'asc')->pluck('title');
    }

    public function addLinkId($sourceId, $linkId)
    {
        return DataSourceIgnore::create([
            'source_id' => $sourceId,
            'link_id' => $linkId,
        ]);
    }

    public function addTitle($sourceId, $title)
    {
        return DataSourceIgnore::create([
            'source_id' => $sourceId,
            'title' => $title,
        ]);
    }

    public function deleteByLinkId($sourceId, $linkId)
    {
        DataSourceIgnore::where('source_id', $sourceId)->where('link_id', $linkId)->delete();
    }

    public function deleteByTitle($sourceId, $title)
    {
        DataSourceIgnore::where('source_id', $sourceId)->where('title', $title)->delete();
    }
}