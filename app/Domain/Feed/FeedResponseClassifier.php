<?php

namespace App\Domain\Feed;

/**
 * Decides whether a response is a feed before anything tries to parse it.
 *
 * Without this, a bot check page went straight to simplexml_load_string(), and the parser error
 * - plus the whole page, appended by the Loader - became the feed's status: ~150 lines of SVG and
 * obfuscated JavaScript per feed in the cron log and the Feed Links table. Seen on 2026-10-16,
 * when six of 26 feeds refused the new server's IP (improvements #174, #177).
 *
 * The body is sniffed rather than trusting Content-Type: feeds are served as text/xml,
 * application/rss+xml and sometimes text/html, and bot check pages come back as 200 as often
 * as 403.
 */
class FeedResponseClassifier
{
    /**
     * How far into the body to look for the root element. Feeds can open with an XML
     * declaration, a stylesheet instruction and comments before it.
     */
    const SNIFF_LENGTH = 4096;

    /**
     * Markers for bot check pages, checked in order. Each was taken from a real response.
     */
    const BLOCK_MARKERS = [
        // A host-level check served the same 12KB page for five feeds on 2026-10-16, behind
        // Cloudflare, nginx and openresty alike: title "One moment, please...".
        FeedRunOutcome::BLOCKED_VERIFICATION => ['Please wait while your request is being verified'],
        // Mondo Nintendo, 403, "Visitor anti-robot validation".
        FeedRunOutcome::BLOCKED_BITNINJA => ['bitninja'],
        FeedRunOutcome::BLOCKED_CLOUDFLARE => [
            'challenge-platform', 'cf-chl', '<title>Just a moment...</title>', 'Attention Required! | Cloudflare',
        ],
        FeedRunOutcome::BLOCKED_CAPTCHA => ['captcha'],
    ];

    const BLOCK_DESCRIPTIONS = [
        FeedRunOutcome::BLOCKED_VERIFICATION => 'bot check page ("Please wait while your request is being verified")',
        FeedRunOutcome::BLOCKED_BITNINJA => 'BitNinja captcha page',
        FeedRunOutcome::BLOCKED_CLOUDFLARE => 'Cloudflare challenge',
        FeedRunOutcome::BLOCKED_CAPTCHA => 'captcha page',
    ];

    /**
     * @return array{outcome: string, message: string}
     */
    public function classify(FeedResponse $response): array
    {
        $status = $response->statusCode;
        $statusText = $status === null ? '' : ', HTTP '.$status;

        // A bot check page is reported as a block whatever its status, as long as it isn't a
        // feed: a real feed could mention "captcha" in a review.
        $looksLikeXml = $this->looksLikeXml($response->body);
        if (!$looksLikeXml) {
            $blocked = $this->findBlock($response);
            if ($blocked !== null) {
                return $this->result($blocked, 'Blocked: '.self::BLOCK_DESCRIPTIONS[$blocked].$statusText);
            }
        }

        if ($status !== null && $status !== 200) {
            return $this->result(FeedRunOutcome::HTTP_ERROR, 'HTTP '.$status.' from the feed URL');
        }

        if (trim($response->body) === '') {
            return $this->result(FeedRunOutcome::EMPTY_RESPONSE, 'Empty response'.$statusText);
        }

        if (!$looksLikeXml) {
            $got = $response->contentType ? 'got '.$this->shortContentType($response->contentType) : 'not XML';
            $title = $this->pageTitle($response->body);
            $message = 'Not a feed: '.$got.($title !== null ? ' (page title: "'.$title.'")' : '');
            return $this->result(FeedRunOutcome::NOT_A_FEED, $message);
        }

        return $this->result(FeedRunOutcome::OK, 'OK');
    }

    /**
     * Throws unless the response looks like a feed.
     *
     * @throws FeedLoadException
     */
    public function assertFeed(FeedResponse $response): void
    {
        $result = $this->classify($response);
        if ($result['outcome'] !== FeedRunOutcome::OK) {
            throw new FeedLoadException($result['message'], $result['outcome'], $response);
        }
    }

    /**
     * True when the body starts like an XML document rather than an HTML page. Any root
     * element counts, not just rss/feed: some partner feeds use their own (item node "post").
     */
    private function looksLikeXml(string $body): bool
    {
        $start = ltrim(mb_substr($body, 0, self::SNIFF_LENGTH), "\xEF\xBB\xBF \t\r\n");

        if ($start === '' || $start[0] !== '<') {
            return false;
        }
        if (preg_match('/^<(!doctype\s+html|html[\s>])/i', $start)) {
            return false;
        }
        if (str_starts_with($start, '<?xml')) {
            return true;
        }
        // No declaration: accept a root that isn't HTML.
        return preg_match('/<(rss|feed|rdf:RDF|channel|posts?|items?)[\s>]/i', $start) === 1;
    }

    private function findBlock(FeedResponse $response): ?string
    {
        if (strtolower((string) $response->header('cf-mitigated')) === 'challenge') {
            return FeedRunOutcome::BLOCKED_CLOUDFLARE;
        }

        foreach (self::BLOCK_MARKERS as $outcome => $markers) {
            foreach ($markers as $marker) {
                if (stripos($response->body, $marker) !== false) {
                    return $outcome;
                }
            }
        }

        return null;
    }

    private function pageTitle(string $body): ?string
    {
        if (!preg_match('/<title[^>]*>(.*?)<\/title>/is', $body, $m)) {
            return null;
        }
        $title = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($m[1]))));
        return $title === '' ? null : mb_strimwidth($title, 0, 80, '...');
    }

    private function shortContentType(string $contentType): string
    {
        return trim(explode(';', $contentType)[0]);
    }

    private function result(string $outcome, string $message): array
    {
        return ['outcome' => $outcome, 'message' => $message];
    }
}
