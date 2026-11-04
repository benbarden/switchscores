<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Carries IDs rather than the wishlist row, as the row is already deleted when this fires.
 */
class GameWishlistRemoved
{
    use Dispatchable;

    public function __construct(
        public int $userId,
        public int $gameId
    )
    {
    }
}
