<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RejectBotsOnIntentRoutes;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RejectBotsOnIntentRoutesTest extends TestCase
{
    private function handleWithUserAgent(?string $userAgent): Response
    {
        $request = Request::create('/members/intent/wishlist-add/9408');

        if ($userAgent !== null) {
            $request->headers->set('User-Agent', $userAgent);
        }

        return (new RejectBotsOnIntentRoutes())->handle(
            $request,
            fn () => new Response('passed through')
        );
    }

    private function assertGone(string $userAgent): void
    {
        try {
            $this->handleWithUserAgent($userAgent);
        } catch (HttpException $e) {
            $this->assertSame(410, $e->getStatusCode(), "Expected 410 for user agent: {$userAgent}");
            return;
        }

        $this->fail("Expected a 410 for user agent: {$userAgent}");
    }

    private function assertPassesThrough(string $userAgent): void
    {
        $response = $this->handleWithUserAgent($userAgent);

        $this->assertSame('passed through', $response->getContent(), "Expected pass-through for user agent: {$userAgent}");
    }

    /**
     * The regression this test exists for: only 'googlebot' was matched, so GoogleOther fetches
     * got a login redirect instead of the 410 (filing the URL under "Page with redirect" in GSC
     * rather than removing it), and GSC's Test Live URL - which sends Google-InspectionTool -
     * reported a redirect rather than the 410 the URL actually serves.
     */
    public function testReturnsGoneForGoogleNonGooglebotCrawlers()
    {
        $this->assertGone('Mozilla/5.0 (compatible; GoogleOther)');
        $this->assertGone('Mozilla/5.0 (compatible; Google-InspectionTool/1.0)');
        $this->assertGone('Mozilla/5.0 (compatible; Google-Extended)');
        $this->assertGone('AdsBot-Google (+http://www.google.com/adsbot.html)');
    }

    public function testReturnsGoneForGooglebot()
    {
        $this->assertGone('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
        $this->assertGone('Googlebot-Image/1.0');
    }

    public function testReturnsGoneForOtherSearchEngineCrawlers()
    {
        $this->assertGone('Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)');
        $this->assertGone('Mozilla/5.0 (compatible; BingPreview/1.0b)');
        $this->assertGone('Mozilla/5.0 (compatible; YandexBot/3.0)');
        $this->assertGone('Mozilla/5.0 (compatible; Baiduspider/2.0)');
        $this->assertGone('DuckDuckBot/1.1; (+http://duckduckgo.com/duckduckbot.html)');
        $this->assertGone('Mozilla/5.0 (compatible; Yahoo! Slurp)');
    }

    /**
     * Guests must keep reaching the auth middleware so the signup-intent flow still works. A UA
     * containing "google" or "bing" incidentally must NOT be treated as a crawler - hence the
     * explicit token list rather than a bare substring match.
     */
    public function testPassesThroughForRealVisitors()
    {
        $this->assertPassesThrough('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36');
        $this->assertPassesThrough('Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1');
        $this->assertPassesThrough('Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Mobile Safari/537.36 GoogleApp/15.30');
    }

    public function testPassesThroughWhenNoUserAgentIsSent()
    {
        $response = $this->handleWithUserAgent(null);

        $this->assertSame('passed through', $response->getContent());
    }
}
