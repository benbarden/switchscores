<?php

namespace App\Domain\BuyersGuide;

use Carbon\Carbon;

/**
 * Whether the buyer's guide banner shows: site-wide on public pages and the members home, up to
 * and including the banner_until date in config/buyers-guide.php, and never on the guide's own
 * pages.
 */
class Banner
{
    const GUIDE_ROUTES = ['buyersGuide.landing', 'buyersGuide.region'];

    public function isShown(?string $routeName): bool
    {
        if (in_array($routeName, self::GUIDE_ROUTES, true)) {
            return false;
        }

        $until = config('buyers-guide.banner_until');
        if (!$until) {
            return false;
        }

        return now()->lte(Carbon::parse($until)->endOfDay());
    }
}
