<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\DbEditGame;


class DbEditGameService
{
    public function getStatusList()
    {
        $statuses = [];
        $statuses[] = ['id' => DbEditGame::STATUS_PENDING, 'title' => 'Pending'];
        $statuses[] = ['id' => DbEditGame::STATUS_APPROVED, 'title' => 'Approved'];
        $statuses[] = ['id' => DbEditGame::STATUS_DENIED, 'title' => 'Denied'];
        return $statuses;
    }

    /**
     * @param $id
     * @return DbEditGame
     */
    public function find($id)
    {
        return DbEditGame::find($id);
    }

    public function getCategoryEditsByStatus($status)
    {
        return DbEditGame::where('data_to_update', DbEditGame::DATA_CATEGORY)->where('status', $status)->get();
    }

    public function getAllCategoryEdits()
    {
        return DbEditGame::where('data_to_update', DbEditGame::DATA_CATEGORY)->get();
    }

    public function getPendingCategoryEdits()
    {
        $status = DbEditGame::STATUS_PENDING;
        return $this->getCategoryEditsByStatus($status);
    }

    public function getPendingCategoryEditsGameIdList()
    {
        return DbEditGame::
            where('data_to_update', DbEditGame::DATA_CATEGORY)
            ->where('status', DbEditGame::STATUS_PENDING)
            ->orderBy('game_id', 'asc')
            ->pluck('game_id');
    }

    public function getApprovedCategoryEdits()
    {
        $status = DbEditGame::STATUS_APPROVED;
        return $this->getCategoryEditsByStatus($status);
    }

    public function getDeniedCategoryEdits()
    {
        $status = DbEditGame::STATUS_DENIED;
        return $this->getCategoryEditsByStatus($status);
    }
}