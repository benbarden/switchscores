<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;

use App\Events\GameCollectionAdded;
use App\Models\ActivityLog;
use App\Domain\UserGamesCollection;
use App\Domain\ActivityLog\Repository as ActivityLogRepository;

class LogGameCollectionAdded
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        private ActivityLogRepository $repoActivityLog
    )
    {
    }

    /**
     * Handle the event.
     *
     * @param GameCollectionAdded $event
     * @return void
     */
    public function handle(GameCollectionAdded $event)
    {
        $userGamesCollection = $event->userGamesCollection;
        $userId = $userGamesCollection->user->id;

        $this->repoActivityLog->create(
            ActivityLog::EVENT_TYPE_USER_GAME_COLLECTION_ADDED,
            $userId,
            'App\Domain\UserGamesCollection',
            $userGamesCollection->game->id
        );
    }
}
