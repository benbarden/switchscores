<?php


namespace App\Services;

use App\Models\DataSource;

class DataSourceService
{
    public function getAll()
    {
        return DataSource::orderBy('id', 'asc')->get();
    }

    public function getByName($name)
    {
        return DataSource::where('name', $name)->first();
    }

    public function find($sourceId)
    {
        return DataSource::find($sourceId);
    }

    public function getSourceNintendoCoUk()
    {
        return $this->getByName(DataSource::SOURCE_NINTENDO_CO_UK);
    }

    public function getSourceWikipedia()
    {
        return $this->getByName(DataSource::SOURCE_WIKIPEDIA);
    }
}