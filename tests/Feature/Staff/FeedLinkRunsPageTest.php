<?php

namespace Tests\Feature\Staff;

use App\Domain\Feed\FeedRunOutcome;
use App\Domain\PartnerFeedLink\RunRepository;
use App\Models\PartnerFeedLink;
use App\Models\ReviewSite;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;

use Tests\TestCase;

/**
 * The runs page shows response bodies from other people's servers, so they must come out
 * escaped. Writes to the development database; the feed link carries the marker below and is
 * removed in setUp and tearDown (its runs cascade).
 */
class FeedLinkRunsPageTest extends TestCase
{
    const MARKER = 'runs-page-test.invalid';
    const USER_EMAIL = 'runs.page.test@switchscores.com';

    private $feedLink;

    public function setUp(): void
    {
        parent::setUp();

        $this->cleanUp();

        $site = ReviewSite::first();
        if (!$site) {
            $this->markTestSkipped('Needs at least one review site in the database.');
        }

        $this->feedLink = PartnerFeedLink::create([
            'feed_status' => PartnerFeedLink::FEED_STATUS_LIVE,
            'site_id' => $site->id,
            'feed_url' => 'https://'.self::MARKER.'/feed',
            'data_type' => PartnerFeedLink::DATA_TYPE_ARRAY,
            'item_node' => PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM,
        ]);

        $user = new User([
            'display_name' => 'Rita the Review Manager',
            'email' => self::USER_EMAIL,
            'is_staff' => 1
        ]);
        $user->addRole(UserRole::ROLE_REVIEWS_MANAGER);
        $this->be($user);
    }

    public function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    private function cleanUp()
    {
        PartnerFeedLink::where('feed_url', 'like', '%'.self::MARKER.'%')->delete();
        // The request middleware saves the logged-in user (last_access_date), so it has to go too.
        User::where('email', self::USER_EMAIL)->delete();
    }

    public function testRunsAreListedAndTheResponseIsEscaped()
    {
        (new RunRepository())->record($this->feedLink->id, [
            'run_at' => Carbon::now(),
            'was_successful' => false,
            'outcome' => FeedRunOutcome::BLOCKED_VERIFICATION,
            'http_status' => 200,
            'content_type' => 'text/html',
            'message' => 'Blocked: bot check page, HTTP 200',
            'body_excerpt' => '<script>alert("from the feed")</script>',
        ]);

        $response = $this->get(route('staff.reviews.feedLinks.runs', ['linkId' => $this->feedLink->id]));

        $response->assertStatus(200);
        $response->assertSee('Blocked: bot check');
        $response->assertSee('Blocked: bot check page, HTTP 200');
        $response->assertDontSee('<script>alert("from the feed")</script>', false);
        $response->assertSee('&lt;script&gt;', false);
    }

    public function testAFeedLinkWithNoRunsSaysSo()
    {
        $response = $this->get(route('staff.reviews.feedLinks.runs', ['linkId' => $this->feedLink->id]));

        $response->assertStatus(200);
        $response->assertSee('No runs recorded yet');
    }

    public function testAnUnknownFeedLinkIs404()
    {
        $this->get(route('staff.reviews.feedLinks.runs', ['linkId' => 999999999]))->assertStatus(404);
    }

    public function testTheFeedLinksListShowsTheBadgeAndLinksToTheRuns()
    {
        $this->feedLink->last_run_status = 'Blocked: BitNinja captcha page, HTTP 403';
        $this->feedLink->last_run_outcome = FeedRunOutcome::BLOCKED_BITNINJA;
        $this->feedLink->last_run_at = Carbon::now();
        $this->feedLink->save();

        $response = $this->get(route('staff.reviews.feedLinks.index'));

        $response->assertStatus(200);
        $response->assertSee('Blocked: BitNinja');
        $response->assertSee(route('staff.reviews.feedLinks.runs', ['linkId' => $this->feedLink->id]), false);
    }
}
