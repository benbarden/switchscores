<?php


namespace App\Services;

use App\Models\UserGamesCollection;


class UserGamesCollectionService
{
    public function create($userId, $gameId, $ownedFrom, $ownedType, $hoursPlayed, $playStatus)
    {
        return UserGamesCollection::create([
            'user_id' => $userId,
            'game_id' => $gameId,
            'owned_from' => $ownedFrom,
            'owned_type' => $ownedType,
            'hours_played' => $hoursPlayed,
            'play_status' => $playStatus,
        ]);
    }

    public function edit(
        UserGamesCollection $collection, $ownedFrom, $ownedType, $hoursPlayed, $playStatus
    )
    {
        $values = [
            'owned_from' => $ownedFrom,
            'owned_type' => $ownedType,
            'hours_played' => $hoursPlayed,
            'play_status' => $playStatus,
        ];

        $collection->fill($values);
        $collection->save();
    }

    public function delete($collectionId)
    {
        UserGamesCollection::where('id', $collectionId)->delete();
    }

    public function deleteByUserId($userId)
    {
        UserGamesCollection::where('user_id', $userId)->delete();
    }

    public function find($id)
    {
        return UserGamesCollection::find($id);
    }

    public function getByUser($userId, $limit = null)
    {
        $items = UserGamesCollection::
            where('user_id', $userId)
            ->orderBy('owned_from', 'desc')
            ->orderBy('id', 'desc');

        if ($limit) {
            $items = $items->limit($limit);
        }

        $items = $items->get();

        return $items;
    }

    public function getGameIdsByUser($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->pluck('game_id');
    }

    public function isGameInCollection($userId, $gameId)
    {
        $item = UserGamesCollection::where('user_id', $userId)->where('game_id', $gameId)->first();
        if ($item) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserGameItem($userId, $gameId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('game_id', $gameId)->first();
    }

    public function countAllCollections()
    {
        return UserGamesCollection::count();
    }
}