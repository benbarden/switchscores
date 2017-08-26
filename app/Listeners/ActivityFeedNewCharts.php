<?php

namespace App\Listeners;

use App\Events\ChartsCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivityFeedNewCharts
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
     * @param  ChartsCreated  $event
     * @return void
     */
    public function handle(ChartsCreated $event)
    {
        //
    }
}
