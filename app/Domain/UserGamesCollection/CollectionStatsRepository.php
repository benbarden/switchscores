<?php

namespace App\Domain\UserGamesCollection;

use App\Models\UserGamesCollection;

class CollectionStatsRepository
{
    public function userTotalGames($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->count();
    }

    public function userTotalHours($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->sum('hours_played');
    }

    public function userTotalPlayStatus($userId, $playStatus)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', $playStatus)->count();
    }

    public function userTotalNotStarted($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_NOT_STARTED)->count();
    }

    public function userTotalNowPlaying($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_NOW_PLAYING)->count();
    }

    public function userTotalPaused($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_PAUSED)->count();
    }

    public function userTotalAbandoned($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_ABANDONED)->count();
    }

    public function userTotalCompleted($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_COMPLETED)->count();
    }

    public function userTotalReplaying($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_REPLAYING)->count();
    }

    public function userTotalEndless($userId)
    {
        return UserGamesCollection::where('user_id', $userId)->where('play_status', PlayStatus::PLAY_STATUS_ENDLESS)->count();
    }

    public function userStats($userId)
    {
        $collectionStats = [];
        $collectionStats[] = [
            'title' => 'Total games',
            'count' => $this->userTotalGames($userId)
        ];
        $collectionStats[] = [
            'title' => 'Total hours logged',
            'count' => $this->userTotalHours($userId)
        ];

        $collPlayStatus = new PlayStatus();

        $playStatusList = $collPlayStatus->generateAll();

        foreach ($playStatusList as $item) {

            $statusId = $item->getId();
            $itemCount = $this->userTotalPlayStatus($userId, $statusId);

            $collectionStats[] = [
                'playStatus' => $item,
                'count' => $itemCount
            ];

        }

        return $collectionStats;
    }

}