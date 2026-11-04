<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class GameWishlistAdded
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $gameId
    )
    {
    }
}
