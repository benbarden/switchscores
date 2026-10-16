<?php

namespace App\Domain\Feed;

/**
 * What happened when a feed was loaded or imported, as a short code.
 *
 * Stored on partner_feed_links.last_run_outcome and partner_feed_link_runs.outcome, and turned
 * into a label and a badge colour for the staff pages. Blocked outcomes get their own codes
 * because they need a different response from a broken feed: the feed itself is fine, but the
 * site won't serve it to our server's IP, so the fix is to ask the site, not to edit the feed link.
 */
class FeedRunOutcome
{
    const OK = 'ok';

    const BLOCKED_VERIFICATION = 'blocked_verification';
    const BLOCKED_CLOUDFLARE = 'blocked_cloudflare';
    const BLOCKED_BITNINJA = 'blocked_bitninja';
    const BLOCKED_CAPTCHA = 'blocked_captcha';

    const HTTP_ERROR = 'http_error';
    const CONNECTION_ERROR = 'connection_error';
    const EMPTY_RESPONSE = 'empty';
    const NOT_A_FEED = 'not_a_feed';
    const PARSE_ERROR = 'parse_error';
    const IMPORT_ERROR = 'import_error';
    const RELAY_ERROR = 'relay_error';
    const ERROR = 'error';

    const LABELS = [
        self::OK => 'OK',
        self::BLOCKED_VERIFICATION => 'Blocked: bot check',
        self::BLOCKED_CLOUDFLARE => 'Blocked: Cloudflare',
        self::BLOCKED_BITNINJA => 'Blocked: BitNinja',
        self::BLOCKED_CAPTCHA => 'Blocked: captcha',
        self::HTTP_ERROR => 'HTTP error',
        self::CONNECTION_ERROR => 'Could not connect',
        self::EMPTY_RESPONSE => 'Empty response',
        self::NOT_A_FEED => 'Not a feed',
        self::PARSE_ERROR => 'Invalid XML',
        self::IMPORT_ERROR => 'Import error',
        self::RELAY_ERROR => 'Relay error',
        self::ERROR => 'Error',
    ];

    public static function label(?string $outcome): string
    {
        return self::LABELS[$outcome] ?? 'Error';
    }

    public static function isBlocked(?string $outcome): bool
    {
        return $outcome !== null && str_starts_with($outcome, 'blocked_');
    }

    /**
     * Bootstrap badge classes: green for OK, amber for blocked (the feed is probably fine; the
     * site is refusing our IP), red for everything else.
     */
    public static function badgeClass(?string $outcome): string
    {
        if ($outcome === self::OK) {
            return 'bg-success';
        }
        if (self::isBlocked($outcome)) {
            return 'bg-warning text-dark';
        }
        return 'bg-danger';
    }
}
