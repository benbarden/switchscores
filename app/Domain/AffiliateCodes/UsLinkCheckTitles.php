<?php

namespace App\Domain\AffiliateCodes;

use Illuminate\Support\Facades\Cache;

/**
 * Amazon's product titles from the last US link check, keyed by ASIN, so the staff list can show
 * what each link points to without calling the API on every page load. Amazon's terms allow its
 * data to be kept for 24 hours at most, so they expire after a day; the nightly run puts them back.
 */
class UsLinkCheckTitles
{
    const CACHE_KEY = 'amazon-us-link-check-titles';

    const CACHE_SECONDS = 86400;

    /**
     * @param array<string, ?string> $titlesByAsin
     */
    public function put(array $titlesByAsin): void
    {
        Cache::put(self::CACHE_KEY, array_filter($titlesByAsin), self::CACHE_SECONDS);
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return Cache::get(self::CACHE_KEY, []);
    }
}
