<?php


namespace App\Services;

use App\GameChangeHistory;


class GameChangeHistoryService
{
    public function create($gameId, $affectedTableName, $source, $changeType, $dataOld, $dataNew, $dataChanged, $userId)
    {
        GameChangeHistory::create([
            'game_id' => $gameId,
            'affected_table_name' => $affectedTableName,
            'source' => $source,
            'change_type' => $changeType,
            'data_old' => $dataOld,
            'data_new' => $dataNew,
            'data_changed' => $dataChanged,
            'user_id' => $userId,
        ]);
    }

    public function find($id)
    {
        return GameChangeHistory::find($id);
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        $itemList = GameChangeHistory::
            orderBy('id', 'desc')
            ->get();
        return $itemList;
    }

    public function getBySource($source)
    {
        $itemList = GameChangeHistory::
            where('source', $source)
            ->orderBy('id', 'desc')
            ->get();
        return $itemList;
    }

    public function getByChangeType($changeType)
    {
        $itemList = GameChangeHistory::
            where('change_history', $changeType)
            ->orderBy('id', 'desc')
            ->get();
        return $itemList;
    }

    public function getByUserId($userId)
    {
        $itemList = GameChangeHistory::
            where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->get();
        return $itemList;
    }
}