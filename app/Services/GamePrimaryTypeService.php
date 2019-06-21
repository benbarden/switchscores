<?php


namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\GamePrimaryType;


class GamePrimaryTypeService
{
    public function create($name, $linkTitle)
    {
        GamePrimaryType::create([
            'primary_type' => $name,
            'link_title' => $linkTitle
        ]);
    }

    public function find($typeId)
    {
        return GamePrimaryType::find($typeId);
    }

    public function delete($typeId)
    {
        GamePrimaryType::where('id', $typeId)->delete();
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        $typeList = GamePrimaryType::
            orderBy('primary_type', 'asc')
            ->get();
        return $typeList;
    }

    public function getByName($name)
    {
        $primaryType = GamePrimaryType::
            where('primary_type', $name)
            ->first();
        return $primaryType;
    }

    public function getByLinkTitle($linkTitle)
    {
        $primaryType = GamePrimaryType::
            where('link_title', $linkTitle)
            ->first();
        return $primaryType;
    }
}