<?php

namespace App\Domain\Scraper;

/**
 * Nintendo serves its "page not found" as a redirect to an error page that returns
 * **HTTP 200**, so a status check alone cannot tell a de-listed game from a live one.
 * The reliable signal is where the request ended up.
 *
 * This started as a private method duplicated in GameCrawlUrl and GameCrawlBatch, which
 * is where the behaviour was first worked out. Lifted here when NintendoPageFetcher
 * needed the same check, rather than making a third copy.
 */
class NintendoPageStatus
{
    /**
     * Nintendo's 404 page URL patterns.
     */
    private const SOFT_404_PATTERNS = [
        '/404.html',
        '/404',
        '/en-gb/404',
        '/en-gb/404.html',
    ];

    /**
     * Did this request end up on Nintendo's error page?
     *
     * @param string $finalUrl The URL after following redirects, not the one requested.
     */
    public static function isSoft404(string $finalUrl): bool
    {
        foreach (self::SOFT_404_PATTERNS as $pattern) {
            if (str_contains($finalUrl, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
