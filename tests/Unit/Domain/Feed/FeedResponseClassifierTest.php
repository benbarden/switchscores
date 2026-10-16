<?php

namespace Tests\Unit\Domain\Feed;

use App\Domain\Feed\FeedLoadException;
use App\Domain\Feed\FeedResponse;
use App\Domain\Feed\FeedResponseClassifier;
use App\Domain\Feed\FeedRunOutcome;

use Tests\TestCase;

/**
 * The classifier decides whether a response is a feed before anything parses it.
 *
 * The block pages here are cut down from real responses seen on 2026-10-16, when the new
 * server's IP was refused by six feeds (improvements #174, #177).
 */
class FeedResponseClassifierTest extends TestCase
{
    const RSS = '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Example</title>'
        .'<item><title>Game Review</title><link>https://example.com/1</link></item></channel></rss>';

    /** The page five feeds returned: 200, text/html, "One moment, please...". */
    const VERIFICATION_PAGE = '<!DOCTYPE html><html><head><title>One moment, please...</title></head>'
        .'<body><svg xmlns="http://www.w3.org/2000/svg"></svg>'
        .'<p>Please wait while your request is being verified...</p>'
        .'<script>var a0I=a0v;(function(d,Q){})</script></body></html>';

    const BITNINJA_PAGE = '<!DOCTYPE html><html><head><title>Visitor anti-robot validation</title></head>'
        .'<body>Protected by BitNinja. Please complete the CAPTCHA.</body></html>';

    private function classify($statusCode, $contentType, $body, $headers = [])
    {
        return (new FeedResponseClassifier())->classify(new FeedResponse($statusCode, $contentType, $body, $headers));
    }

    public function testAnRssFeedIsOk()
    {
        $result = $this->classify(200, 'application/rss+xml; charset=UTF-8', self::RSS);

        $this->assertEquals(FeedRunOutcome::OK, $result['outcome']);
    }

    public function testAFeedServedAsHtmlIsStillOk()
    {
        // Content type is not trusted: some feeds really are served as text/html.
        $result = $this->classify(200, 'text/html', self::RSS);

        $this->assertEquals(FeedRunOutcome::OK, $result['outcome']);
    }

    public function testAFeedWithoutAnXmlDeclarationIsOk()
    {
        $result = $this->classify(200, 'text/xml', '<rss version="2.0"><channel></channel></rss>');

        $this->assertEquals(FeedRunOutcome::OK, $result['outcome']);
    }

    public function testAByteOrderMarkDoesNotHideAFeed()
    {
        $result = $this->classify(200, 'text/xml', "\xEF\xBB\xBF".self::RSS);

        $this->assertEquals(FeedRunOutcome::OK, $result['outcome']);
    }

    public function testAFeedThatMentionsCaptchaIsNotABlock()
    {
        $body = str_replace('Game Review', 'Captcha Quest review', self::RSS);

        $result = $this->classify(200, 'application/rss+xml', $body);

        $this->assertEquals(FeedRunOutcome::OK, $result['outcome']);
    }

    public function testTheVerificationPageIsABlockEvenWithStatus200()
    {
        $result = $this->classify(200, 'text/html', self::VERIFICATION_PAGE);

        $this->assertEquals(FeedRunOutcome::BLOCKED_VERIFICATION, $result['outcome']);
        $this->assertStringContainsString('HTTP 200', $result['message']);
    }

    public function testBitNinjaIsNamed()
    {
        $result = $this->classify(403, 'text/html', self::BITNINJA_PAGE);

        $this->assertEquals(FeedRunOutcome::BLOCKED_BITNINJA, $result['outcome']);
        $this->assertStringContainsString('HTTP 403', $result['message']);
    }

    public function testACloudflareChallengeHeaderIsABlock()
    {
        $result = $this->classify(403, 'text/html', '<html><body>Checking...</body></html>', ['cf-mitigated' => 'challenge']);

        $this->assertEquals(FeedRunOutcome::BLOCKED_CLOUDFLARE, $result['outcome']);
    }

    public function testAnErrorStatusWithoutAKnownPageIsAnHttpError()
    {
        $result = $this->classify(404, 'text/html', '<html><title>Not found</title></html>');

        $this->assertEquals(FeedRunOutcome::HTTP_ERROR, $result['outcome']);
        $this->assertEquals('HTTP 404 from the feed URL', $result['message']);
    }

    public function testAnEmptyBodyIsReported()
    {
        $result = $this->classify(200, 'application/rss+xml', "  \n");

        $this->assertEquals(FeedRunOutcome::EMPTY_RESPONSE, $result['outcome']);
    }

    public function testAnHtmlPageIsNotAFeedAndTheTitleIsGiven()
    {
        $result = $this->classify(200, 'text/html; charset=UTF-8', '<!doctype html><html><head><title>Reviews | Example</title></head></html>');

        $this->assertEquals(FeedRunOutcome::NOT_A_FEED, $result['outcome']);
        $this->assertEquals('Not a feed: got text/html (page title: "Reviews | Example")', $result['message']);
    }

    /**
     * The point of the whole change: whatever goes wrong, the message is one line and never
     * carries the page.
     */
    public function testAssertFeedThrowsAOneLineMessageAndKeepsTheBodySeparately()
    {
        try {
            (new FeedResponseClassifier())->assertFeed(new FeedResponse(200, 'text/html', self::VERIFICATION_PAGE));
            $this->fail('Expected a FeedLoadException');
        } catch (FeedLoadException $e) {
            $this->assertStringNotContainsString("\n", $e->getMessage());
            $this->assertStringNotContainsString('<svg', $e->getMessage());
            $this->assertEquals(FeedRunOutcome::BLOCKED_VERIFICATION, $e->outcome);
            $this->assertStringContainsString('<svg', $e->response->bodyExcerpt());
        }
    }
}
