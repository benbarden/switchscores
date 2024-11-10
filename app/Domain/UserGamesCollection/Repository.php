<?php


namespace App\Domain\UserGamesCollection;


use App\Models\UserGamesCollection;

class Repository
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

    public function byUserGameItem($userId, $gameId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('game_id', $gameId)->first();
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

    public function byUserAndPlayStatus($userId, $playStatus, $limit = null)
    {
        $items = UserGamesCollection::
            where('user_id', $userId)
            ->where('play_status', $playStatus)
            ->orderBy('owned_from', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        if ($limit) {
            $items = $items->limit($limit);
        }

        $items = $items->get();
        return $items;
    }

    public function byUserNowPlaying($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_NOW_PLAYING;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }

    public function byUserPaused($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_PAUSED;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }

    public function byUserNotStarted($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_NOT_STARTED;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }

    public function byUserAbandoned($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_ABANDONED;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }

    public function byUserCompleted($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_COMPLETED;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }

    public function byUserReplaying($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_REPLAYING;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }

    public function byUserEndless($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_ENDLESS;
        return $this->byUserAndPlayStatus($userId, $statusId, $limit);
    }
}