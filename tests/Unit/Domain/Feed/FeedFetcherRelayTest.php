<?php

namespace Tests\Unit\Domain\Feed;

use App\Domain\Feed\FeedFetcher;
use App\Domain\Feed\FeedLoadException;
use App\Domain\Feed\FeedRunOutcome;
use App\Domain\Relay\EgressRelay;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

use Tests\TestCase;

/**
 * Fetching a feed through the egress relay (#174). The network is a Guzzle MockHandler, which
 * also records the request that would have been sent.
 */
class FeedFetcherRelayTest extends TestCase
{
    const FEED_URL = 'https://www.cubed3.com/feeds/switch';
    const RSS = '<?xml version="1.0"?><rss version="2.0"><channel><title>Cubed3</title></channel></rss>';

    private function relay(): EgressRelay
    {
        return new EgressRelay('https://relay.example/', 'secret');
    }

    public function testARelayedFetchGoesToTheRelayWithTheKey()
    {
        $mock = new MockHandler([new Response(200, ['Content-Type' => 'application/rss+xml'], self::RSS)]);

        $body = (new FeedFetcher($this->relay(), $mock))->fetch(self::FEED_URL, true);

        $this->assertEquals(self::RSS, $body);
        $request = $mock->getLastRequest();
        $this->assertEquals('relay.example', $request->getUri()->getHost());
        parse_str($request->getUri()->getQuery(), $params);
        $this->assertEquals(self::FEED_URL, $params['url']);
        $this->assertEquals('secret', $request->getHeaderLine('X-Relay-Key'));
    }

    public function testADirectFetchIgnoresTheRelay()
    {
        $mock = new MockHandler([new Response(200, ['Content-Type' => 'application/rss+xml'], self::RSS)]);

        (new FeedFetcher($this->relay(), $mock))->fetch(self::FEED_URL, false);

        $request = $mock->getLastRequest();
        $this->assertEquals('www.cubed3.com', $request->getUri()->getHost());
        $this->assertFalse($request->hasHeader('X-Relay-Key'));
    }

    public function testTheRelayRejectingTheKeyIsAReadableRelayError()
    {
        $mock = new MockHandler([new Response(403, ['Content-Type' => 'text/plain'], 'Forbidden')]);

        try {
            (new FeedFetcher($this->relay(), $mock))->fetch(self::FEED_URL, true);
            $this->fail('Expected a FeedLoadException');
        } catch (FeedLoadException $e) {
            $this->assertEquals(FeedRunOutcome::RELAY_ERROR, $e->outcome);
            $this->assertStringContainsString('rejected the key', $e->getMessage());
        }
    }

    public function testAHostMissingFromTheAllowlistSaysSo()
    {
        $mock = new MockHandler([new Response(400, ['Content-Type' => 'text/plain'], 'Host not allowed')]);

        try {
            (new FeedFetcher($this->relay(), $mock))->fetch(self::FEED_URL, true);
            $this->fail('Expected a FeedLoadException');
        } catch (FeedLoadException $e) {
            $this->assertEquals(FeedRunOutcome::RELAY_ERROR, $e->outcome);
            $this->assertStringContainsString('allowlist', $e->getMessage());
        }
    }

    /**
     * The target's own block page, passed through by the relay, is still classified as a block -
     * not mistaken for the relay refusing.
     */
    public function testATargetBlockThroughTheRelayIsStillABlock()
    {
        $page = '<html><title>One moment, please...</title><p>Please wait while your request is being verified...</p></html>';
        $mock = new MockHandler([new Response(200, ['Content-Type' => 'text/html'], $page)]);

        try {
            (new FeedFetcher($this->relay(), $mock))->fetch(self::FEED_URL, true);
            $this->fail('Expected a FeedLoadException');
        } catch (FeedLoadException $e) {
            $this->assertEquals(FeedRunOutcome::BLOCKED_VERIFICATION, $e->outcome);
        }
    }

    public function testRelayingWithoutConfigurationFailsClearly()
    {
        $mock = new MockHandler([]);

        try {
            (new FeedFetcher(new EgressRelay(null, null), $mock))->fetch(self::FEED_URL, true);
            $this->fail('Expected a FeedLoadException');
        } catch (FeedLoadException $e) {
            $this->assertEquals(FeedRunOutcome::RELAY_ERROR, $e->outcome);
            $this->assertStringContainsString('RELAY_URL', $e->getMessage());
            // Fails before any request is made, rather than quietly going direct.
            $this->assertNull($mock->getLastRequest());
        }
    }
}
