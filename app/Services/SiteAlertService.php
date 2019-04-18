<?php


namespace App\Services;

use App\SiteAlert;


class SiteAlertService
{
    public function create($type, $source, $detail)
    {
        SiteAlert::create([
            'type' => $type,
            'source' => $source,
            'detail' => $detail,
        ]);
    }

    public function find($id)
    {
        return SiteAlert::find($id);
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        $itemList = SiteAlert::
            orderBy('id', 'desc')
            ->get();
        return $itemList;
    }

    public function getByType($type)
    {
        $itemList = SiteAlert::
            where('type', $type)
            ->orderBy('id', 'dsc')
            ->get();
        return $itemList;
    }

    public function countByType($type)
    {
        $itemList = SiteAlert::
            where('type', $type)
            ->count();
        return $itemList;
    }
}