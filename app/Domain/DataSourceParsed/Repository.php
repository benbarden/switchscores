<?php

namespace App\Domain\DataSourceParsed;

use App\Models\DataSource;
use App\Models\DataSourceParsed;

use Illuminate\Support\Facades\DB;

class Repository
{
    /**
     * @param $itemId
     * @return DataSourceParsed
     */
    public function find($itemId)
    {
        return DataSourceParsed::find($itemId);
    }

    public function getAllBySource($sourceId)
    {
        return DataSourceParsed::where('source_id', $sourceId)->orderBy('title', 'asc')->get();
    }

    public function getAllBySourceWithGameId($sourceId)
    {
        return DataSourceParsed::where('source_id', $sourceId)->whereNotNull('game_id')->orderBy('game_id', 'asc')->get();
    }

    public function getAllBySourceWithNoGameId($sourceId, $excludeLinkIdList = null, $excludeTitleList = null)
    {
        $dsItemList = DataSourceParsed::where('source_id', $sourceId)->whereNull('game_id');
        if ($excludeLinkIdList) {
            $dsItemList = $dsItemList->whereNotIn('link_id', $excludeLinkIdList);
        }
        if ($excludeTitleList) {
            $dsItemList = $dsItemList->whereNotIn('title', $excludeTitleList);
        }
        $dsItemList = $dsItemList->orderBy('title', 'asc')->get();
        return $dsItemList;
    }

    public function getBySourceNoGameIdWithEUDate($sourceId, $excludeLinkIdList = null, $excludeTitleList = null)
    {
        $dsItemList = DataSourceParsed
            ::where('source_id', $sourceId)
            ->whereNull('game_id')
            ->whereNotNull('release_date_eu');
        if ($excludeLinkIdList) {
            $dsItemList = $dsItemList->whereNotIn('link_id', $excludeLinkIdList);
        }
        if ($excludeTitleList) {
            $dsItemList = $dsItemList->whereNotIn('title', $excludeTitleList);
        }
        $dsItemList = $dsItemList->orderBy('release_date_eu', 'asc')->orderBy('title', 'asc')->get();
        return $dsItemList;
    }

    public function getBySourceNoGameIdNoEUDate($sourceId, $excludeLinkIdList = null, $excludeTitleList = null)
    {
        $dsItemList = DataSourceParsed
            ::where('source_id', $sourceId)
            ->whereNull('game_id')
            ->whereNull('release_date_eu');
        if ($excludeLinkIdList) {
            $dsItemList = $dsItemList->whereNotIn('link_id', $excludeLinkIdList);
        }
        if ($excludeTitleList) {
            $dsItemList = $dsItemList->whereNotIn('title', $excludeTitleList);
        }
        $dsItemList = $dsItemList->orderBy('title', 'asc')->get();
        return $dsItemList;
    }

    public function getBySourceAndGame($sourceId, $gameId)
    {
        return DataSourceParsed::where('source_id', $sourceId)->where('game_id', $gameId)->first();
    }

    public function getBySourceAndLinkId($sourceId, $linkId)
    {
        return DataSourceParsed::where('source_id', $sourceId)->where('link_id', $linkId)->first();
    }

    public function deleteBySourceId($sourceId)
    {
        DataSourceParsed::where('source_id', $sourceId)->delete();
    }

    public function removeSwitchEshopItems($gameId)
    {
        $sourceId = DataSource::DSID_SWITCH_ESHOP_UK;
        DataSourceParsed::where('source_id', $sourceId)->where('game_id', $gameId)->delete();
    }

    // ********** Nintendo.co.uk ********** //

    public function getAllNintendoCoUkWithGameId()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return $this->getAllBySourceWithGameId($sourceId);
    }

    public function getAllNintendoCoUkWithNoGameId($excludeLinkIdList = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return $this->getAllBySourceWithNoGameId($sourceId, $excludeLinkIdList);
    }

    public function getNintendoCoUkUnlinkedWithEUDate($excludeLinkIdList = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return $this->getBySourceNoGameIdWithEUDate($sourceId, $excludeLinkIdList);
    }

    public function getNintendoCoUkUnlinkedNoEUDate($excludeLinkIdList = null)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return $this->getBySourceNoGameIdNoEUDate($sourceId, $excludeLinkIdList);
    }

    public function getAllNintendoCoUkInLinkIdList($linkIdList)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;

        $dsItemList = DataSourceParsed::where('source_id', $sourceId)
            ->whereIn('link_id', $linkIdList)
            ->orderBy('id', 'desc')
            ->get();

        return $dsItemList;
    }

    public function getSourceNintendoCoUkForGame($gameId)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return $this->getBySourceAndGame($sourceId, $gameId);
    }

    public function getNintendoCoUkByLinkId($linkId)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        return $this->getBySourceAndLinkId($sourceId, $linkId);
    }

    public function clearGameIdFromNintendoCoUkItems($gameId)
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        $dsParsedItem = DataSourceParsed::where('source_id', $sourceId)->where('game_id', $gameId)->get();
        if ($dsParsedItem->count() > 0) {
            foreach ($dsParsedItem as $item) {
                $item->game_id = null;
                $item->save();
            }
        }
    }

    public function updateNintendoCoUkGameIds()
    {
        $sourceId = DataSource::DSID_NINTENDO_CO_UK;
        DB::update('
            UPDATE data_source_parsed dsp, games g
            SET dsp.game_id = g.id
            WHERE dsp.link_id = g.eshop_europe_fs_id
            AND dsp.source_id = ?
        ', [$sourceId]);
    }
}