<?php

namespace App\Listeners;

use App\Events\GameWishlistRemoved;
use App\Models\ActivityLog;
use App\Domain\ActivityLog\Repository as ActivityLogRepository;

class LogGameWishlistRemoved
{
    public function __construct(
        private ActivityLogRepository $repoActivityLog
    )
    {
    }

    public function handle(GameWishlistRemoved $event)
    {
        // The model ID is the game ID, as for collection events, so the log can show the title
        $this->repoActivityLog->create(
            ActivityLog::EVENT_TYPE_USER_WISHLIST_REMOVED,
            $event->userId,
            ActivityLog::EVENT_MODEL_USER_WISHLIST,
            $event->gameId
        );
    }
}
