<?php


namespace App\Services;

use App\EshopEuropeIgnore;

class EshopEuropeIgnoreService
{
    /**
     * @param $id
     * @return EshopEuropeIgnore
     */
    public function find($id)
    {
        return EshopEuropeIgnore::find($id);
    }

    /**
     * @param $fsId
     * @return EshopEuropeIgnore
     */
    public function getByFsId($fsId)
    {
        return EshopEuropeIgnore::where('fs_id', $fsId)->first();
    }

    public function getAll($limit = null)
    {
        return EshopEuropeIgnore::orderBy('created_at', 'desc')->get();
    }

    public function getIgnoredFsIdList()
    {
        return EshopEuropeIgnore::orderBy('created_at', 'desc')->pluck('fs_id');
    }

    public function add($fsId)
    {
        return EshopEuropeIgnore::create([
            'fs_id' => $fsId,
        ]);
    }

    public function deleteByFsId($fsId)
    {
        EshopEuropeIgnore::where('fs_id', $fsId)->delete();
    }
}