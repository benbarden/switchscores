<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\DataSource;
use App\Models\DataSourceIgnore;
use App\Models\DataSourceParsed;

class Repository
{
    public function getUnlinked()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;

        $dsItemList = DataSourceParsed::where('source_id', $sourceId)->whereNull('game_id');
        $ignoreIdList = DataSourceIgnore::where('source_id', $sourceId)->orderBy('link_id', 'asc')->pluck('link_id');
        if ($ignoreIdList) {
            $dsItemList = $dsItemList->whereNotIn('link_id', $ignoreIdList);
        }
        $dsItemList = $dsItemList->orderBy('title', 'asc')->get();
        return $dsItemList;
    }

    public function getUnlinkedByTitle($title)
    {
        $unlinkedList = $this->getUnlinked();
        return $unlinkedList->where('title', $title)->first();
    }

}