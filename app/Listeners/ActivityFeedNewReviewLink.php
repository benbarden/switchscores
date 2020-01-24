<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Traits\SwitchServices;

use App\Events\ReviewLinkCreated;

class ActivityFeedNewReviewLink
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
     * @param  ReviewLinkCreated  $event
     * @return void
     */
    public function handle(ReviewLinkCreated $event)
    {
        $properties = [
            'review_id' => $event->reviewLink->id,
        ];
        $jsonProperties = json_encode($properties);

        $this->getServiceActivityFeed()->createNewReview($jsonProperties);
    }
}
