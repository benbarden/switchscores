<?php

namespace App\Events;

use App\ReviewLink;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ReviewLinkCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var ReviewLink
     */
    public $reviewLink;

    /**
     * Create a new event instance.
     * @param ReviewLink $reviewLink
     * @return void
     */
    public function __construct(ReviewLink $reviewLink)
    {
        $this->reviewLink = $reviewLink;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
