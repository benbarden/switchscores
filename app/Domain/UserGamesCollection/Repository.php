<?php


namespace App\Domain\UserGamesCollection;


use App\UserGamesCollection;

class Repository
{
    public function byUser($userId, $limit = null)
    {
        $items = UserGamesCollection::
            where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($limit) {
            $items = $items->limit($limit);
        }

        $items = $items->get();

        return $items;
    }

    public function byUserGameIds($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->orderBy('id', 'desc')->pluck('game_id');
    }
}