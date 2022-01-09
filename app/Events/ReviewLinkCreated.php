<?php

namespace App\Events;

use App\Models\ReviewLink;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewLinkCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var ReviewLink
     */
    public $reviewLink;

    /**
     * Create a new event instance.
     * @param \App\Models\ReviewLink $reviewLink
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
