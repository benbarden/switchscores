<?php


namespace App\Services;

use App\EshopEuropeAlert;

use Illuminate\Support\Facades\DB;

class EshopEuropeAlertService
{
    public function create($gameId, $typeId, $errorMsg, $currentData, $newData)
    {
        EshopEuropeAlert::create([
            'game_id' => $gameId,
            'type' => $typeId,
            'error_message' => $errorMsg,
            'current_data' => $currentData,
            'new_data' => $newData,
        ]);
    }

    public function clearAll()
    {
        DB::statement('TRUNCATE TABLE eshop_europe_alerts');
    }

    public function find($id)
    {
        return EshopEuropeAlert::find($id);
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        $itemList = EshopEuropeAlert::
            orderBy('id', 'asc')
            ->get();
        return $itemList;
    }

    public function getByType($type)
    {
        $itemList = EshopEuropeAlert::
            where('type', $type)
            ->orderBy('id', 'asc')
            ->get();
        return $itemList;
    }

    public function getLatest($type)
    {
        $item = EshopEuropeAlert::
            where('type', $type)
            ->orderBy('id', 'desc')
            ->first();
        return $item;
    }

    public function countByType($type)
    {
        $itemList = EshopEuropeAlert::
            where('type', $type)
            ->count();
        return $itemList;
    }
}