<?php

namespace App\Domain\DataSource;

use App\Models\DataSource;

class Repository
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
}