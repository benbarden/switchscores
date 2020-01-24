<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Traits\SwitchServices;

use App\Events\GameCreated;

class ActivityFeedNewGame
{
    use SwitchServices;

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

        $this->getServiceActivityFeed()->createNewGame($jsonProperties);
    }
}
