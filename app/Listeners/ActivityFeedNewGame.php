<?php

namespace App\Listeners;

use App\Events\GameCreated;
use App\Services\ActivityFeedService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivityFeedNewGame
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  GameCreated  $event
     * @return void
     */
    public function handle(GameCreated $event)
    {
        $properties = [
            'game_id' => $event->game->id,
        ];
        $jsonProperties = json_encode($properties);

        $activityFeedService = resolve('Services\ActivityFeedService');
        /* @var $activityFeedService ActivityFeedService */
        $activityFeedService->createNewGame($jsonProperties);
    }
}
