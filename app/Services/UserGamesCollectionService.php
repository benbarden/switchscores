<?php


namespace App\Services;

use App\UserGamesCollection;


class UserGamesCollectionService
{
    public function create($userId, $gameId, $ownedFrom, $ownedType, $isStarted, $isOngoing, $isComplete, $hoursPlayed)
    {
        return UserGamesCollection::create([
            'user_id' => $userId,
            'game_id' => $gameId,
            'owned_from' => $ownedFrom,
            'owned_type' => $ownedType,
            'is_started' => $isStarted,
            'is_ongoing' => $isOngoing,
            'is_complete' => $isComplete,
            'hours_played' => $hoursPlayed,
        ]);
    }

    public function edit(
        UserGamesCollection $collection, $ownedFrom, $ownedType, $isStarted, $isOngoing, $isComplete, $hoursPlayed
    )
    {
        $values = [
            'owned_from' => $ownedFrom,
            'owned_type' => $ownedType,
            'is_started' => $isStarted,
            'is_ongoing' => $isOngoing,
            'is_complete' => $isComplete,
            'hours_played' => $hoursPlayed,
        ];

        $collection->fill($values);
        $collection->save();
    }

    public function delete($collectionId)
    {
        UserGamesCollection::where('id', $collectionId)->delete();
    }

    public function find($id)
    {
        return UserGamesCollection::find($id);
    }

    public function getByUser($userId)
    {
        $items = UserGamesCollection::
            where('user_id', $userId)
            ->orderBy('owned_from', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        return $items;
    }
}