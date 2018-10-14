<?php


namespace App\Services;

use App\EshopEuropeGame;


class EshopEuropeGameService
{
    public function find($id)
    {
        return EshopEuropeGame::find($id);
    }

    public function getAll($limit = null)
    {
        $feedItems = EshopEuropeGame
            ::orderBy('created_at', 'desc');

        if ($limit) {
            $feedItems = $feedItems->limit($limit);
        }

        $feedItems = $feedItems->get();

        return $feedItems;
    }

    public function getByTitle($title)
    {
        $eshopGame = EshopEuropeGame::where('title', $title)->first();
        return $eshopGame;
    }

    public function getByFsId($fsId)
    {
        $eshopGame = EshopEuropeGame::where('fs_id', $fsId)->first();
        return $eshopGame;
    }

}