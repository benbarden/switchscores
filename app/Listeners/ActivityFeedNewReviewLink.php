<?php

namespace App\Listeners;

use App\Events\ReviewLinkCreated;
use App\Services\ActivityFeedService;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ActivityFeedNewReviewLink
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
     * @param  ReviewLinkCreated  $event
     * @return void
     */
    public function handle(ReviewLinkCreated $event)
    {
        $properties = [
            'review_id' => $event->reviewLink->id,
        ];
        $jsonProperties = json_encode($properties);

        $activityFeedService = resolve('Services\ActivityFeedService');
        /* @var $activityFeedService ActivityFeedService */
        $activityFeedService->createNewReview($jsonProperties);
    }
}
