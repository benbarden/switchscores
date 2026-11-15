<?php

namespace App\Domain\BuyersGuide;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * The latest Amazon US offers for the buyer's guide (price, saving, image link), keyed by ASIN.
 *
 * Amazon's terms allow API data to be kept for 24 hours at most, and an image only as a link,
 * so this lives in the cache and expires on its own well inside that. The refresh runs hourly;
 * if it stops, the offers expire and the page drops the deals and images rather than showing
 * old prices.
 */
class OfferStore
{
    const CACHE_KEY = 'buyers-guide-offers-us';

    const TTL_SECONDS = 3 * 3600;

    public function put(array $items, Carbon $fetchedAt): void
    {
        Cache::put(self::CACHE_KEY, [
            'fetchedAt' => $fetchedAt->toIso8601String(),
            'items' => $items,
        ], self::TTL_SECONDS);
    }

    /**
     * @return array{fetchedAt: Carbon, items: array}|null
     */
    public function latest(): ?array
    {
        $stored = Cache::get(self::CACHE_KEY);
        if (!is_array($stored) || empty($stored['fetchedAt'])) {
            return null;
        }

        $fetchedAt = Carbon::parse($stored['fetchedAt']);
        if ($fetchedAt->lt(now()->subSeconds(self::TTL_SECONDS))) {
            return null;
        }

        return ['fetchedAt' => $fetchedAt, 'items' => $stored['items'] ?? []];
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
