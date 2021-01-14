<?php


namespace App\Services;

use App\UserGamesCollection;
use App\Services\GamesCollection\PlayStatus;


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

    public function getNowPlayingByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_NOW_PLAYING;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getPausedByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_PAUSED;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getNotStartedByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_NOT_STARTED;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getAbandonedByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_ABANDONED;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getCompletedByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_COMPLETED;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getReplayingByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_REPLAYING;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getEndlessByUser($userId, $limit = null)
    {
        $statusId = PlayStatus::PLAY_STATUS_ENDLESS;
        return $this->getPlayStatusByUser($userId, $statusId, $limit);
    }

    public function getPlayStatusByUser($userId, $playStatus, $limit = null)
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

    public function getUserTotalPlayStatus($userId, $playStatus)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', $playStatus)->count();
    }

    public function getUserTotalNotStarted($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_NOT_STARTED)
            ->count();
    }

    public function getUserTotalNowPlaying($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_NOW_PLAYING)
            ->count();
    }

    public function getUserTotalPaused($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_PAUSED)
            ->count();
    }

    public function getUserTotalAbandoned($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_ABANDONED)
            ->count();
    }

    public function getUserTotalCompleted($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_COMPLETED)
            ->count();
    }

    public function getUserTotalReplaying($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_REPLAYING)
            ->count();
    }

    public function getUserTotalEndless($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_ENDLESS)
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

        $serviceCollectionPlayStatus = new PlayStatus();

        $playStatusList = $serviceCollectionPlayStatus->generateAll();

        foreach ($playStatusList as $playStatus) {

            $statusId = $playStatus->getId();
            $itemCount = $this->getUserTotalPlayStatus($userId, $statusId);

            $collectionStats[] = [
                'playStatus' => $playStatus,
                'count' => $itemCount
            ];

        }

        return $collectionStats;
    }
}