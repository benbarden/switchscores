<?php


namespace App\Services;

use App\UserGamesCollection;


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

    public function getByUserAndPlayStatus($userId, $playStatus)
    {
        $items = UserGamesCollection::
            where('user_id', $userId)
            ->where('play_status', $playStatus)
            ->orderBy('owned_from', 'desc')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        return $items;
    }

    public function getUserTotalGames($userId)
    {
        return UserGamesCollection::where('user_id', $userId)
            ->count();
    }

    public function getUserTotalHours($userId)
    {
        return UserGamesCollection::where('user_id', $userId)
            ->sum('hours_played');
    }

    public function getUserTotalNotStarted($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', UserGamesCollection::PLAY_STATUS_NOT_STARTED)
            ->count();
    }

    public function getUserTotalNowPlaying($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', UserGamesCollection::PLAY_STATUS_NOW_PLAYING)
            ->count();
    }

    public function getUserTotalPaused($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', UserGamesCollection::PLAY_STATUS_PAUSED)
            ->count();
    }

    public function getUserTotalAbandoned($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', UserGamesCollection::PLAY_STATUS_ABANDONED)
            ->count();
    }

    public function getUserTotalCompleted($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', UserGamesCollection::PLAY_STATUS_COMPLETED)
            ->count();
    }

    public function getStats($userId)
    {
        $collectionStats = [];
        $collectionStats[] = [
            'title' => 'Total games',
            'count' => $this->getUserTotalGames($userId)
        ];
        $collectionStats[] = [
            'title' => 'Total hours logged',
            'count' => $this->getUserTotalHours($userId)
        ];

        $userGamesCollection = new UserGamesCollection();

        // Not started
        $collectionStats[] = [
            'count' => $this->getUserTotalNotStarted($userId),
            'playStatus' => $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_NOT_STARTED),
        ];
        $collectionStats[] = [
            'count' => $this->getUserTotalNowPlaying($userId),
            'playStatus' => $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_NOW_PLAYING),
        ];
        $collectionStats[] = [
            'count' => $this->getUserTotalPaused($userId),
            'playStatus' => $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_PAUSED),
        ];
        $collectionStats[] = [
            'count' => $this->getUserTotalAbandoned($userId),
            'playStatus' => $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_ABANDONED),
        ];
        $collectionStats[] = [
            'count' => $this->getUserTotalCompleted($userId),
            'playStatus' => $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_COMPLETED),
        ];

        return $collectionStats;
    }

    public function getPlayStatusList()
    {
        $userGamesCollection = new UserGamesCollection();
        $playStatusList = [];
        $playStatusList[] = $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_NOT_STARTED);
        $playStatusList[] = $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_NOW_PLAYING);
        $playStatusList[] = $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_PAUSED);
        $playStatusList[] = $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_ABANDONED);
        $playStatusList[] = $userGamesCollection->getPlayStatusItem(UserGamesCollection::PLAY_STATUS_COMPLETED);

        return $playStatusList;
    }
}