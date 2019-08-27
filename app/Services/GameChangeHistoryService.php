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

    public function deleteByGameId($gameId)
    {
        GameChangeHistory::where('game_id', $gameId)->delete();
    }

    /**
     * @param null $limit
     * @return mixed
     */
    public function getAll($limit = null)
    {
        $itemList = GameChangeHistory::
            orderBy('id', 'desc');
        if ($limit) {
            $itemList = $itemList->limit($limit);
        }
        $itemList = $itemList->get();
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

    public function getByTable($table)
    {
        $itemList = GameChangeHistory::
            where('affected_table_name', $table)
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

    public function getByGameId($gameId)
    {
        $itemList = GameChangeHistory::
            where('game_id', $gameId)
            ->orderBy('id', 'desc')
            ->get();
        return $itemList;
    }
}