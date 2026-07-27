<?php

namespace Tests\Feature\Staff;

use App\Models\PartnerFeedLink;
use App\Models\ReviewSite;
use App\Models\User;
use App\Models\UserRole;

use Tests\TestCase;

/**
 * Covers the create step of the feed onboarding screen - the only part of it that writes.
 *
 * The probe itself is covered by FeedProbeTest against fixtures; nothing here touches the
 * network.
 *
 * These tests write to the development database, so every record they create carries the
 * marker below and is removed in setUp as well as tearDown. setUp matters: a test that dies
 * midway leaves rows behind, and the next run would then be asserting against a site that
 * already existed rather than one it created.
 */
class FeedLinkProbeCreateTest extends TestCase
{
    const MARKER = 'probe-create-test.invalid';

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->cleanUpTestRecords();

        $user = new User([
            'display_name' => 'Roger the Review Manager',
            'email' => 'probe.create.test@switchscores.com',
            'is_staff' => 1
        ]);
        $user->addRole(UserRole::ROLE_REVIEWS_MANAGER);

        $this->user = $user;

        $this->be($this->user);
    }

    public function tearDown(): void
    {
        $this->cleanUpTestRecords();

        User::where('email', $this->user->email)->delete();
        unset($this->user);

        parent::tearDown();
    }

    private function cleanUpTestRecords()
    {
        PartnerFeedLink::where('feed_url', 'like', '%'.self::MARKER.'%')->delete();
        ReviewSite::where('website_url', 'like', '%'.self::MARKER.'%')->delete();
    }

    private function feedUrl()
    {
        return 'https://'.self::MARKER.'/reviews/feed';
    }

    private function validPayload($overrides = [])
    {
        return array_merge([
            'site_mode' => 'new',
            'name' => 'Probe Create Test Site',
            'link_title' => 'probe-create-test-site',
            'website_url' => 'https://'.self::MARKER.'/',
            'rating_scale' => 10,
            'feed_url' => $this->feedUrl(),
            'feed_status' => PartnerFeedLink::FEED_STATUS_TEST,
            'data_type' => PartnerFeedLink::DATA_TYPE_ARRAY,
            'item_node' => PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM,
            'title_match_rule_pattern' => '(.*) Review',
            'title_match_rule_index' => 1,
        ], $overrides);
    }

    public function testCreatesBothTheSiteAndTheFeedLink()
    {
        $response = $this->post('/staff/reviews/feed-links/probe/create', $this->validPayload());

        $site = ReviewSite::where('website_url', 'https://'.self::MARKER.'/')->first();
        $this->assertNotNull($site, 'Review site was not created');
        $this->assertEquals('Probe Create Test Site', $site->name);
        $this->assertEquals(10, $site->rating_scale);

        $feedLink = PartnerFeedLink::where('feed_url', $this->feedUrl())->first();
        $this->assertNotNull($feedLink, 'Feed link was not created');
        $this->assertEquals($site->id, $feedLink->site_id);
        $this->assertEquals(PartnerFeedLink::DATA_TYPE_ARRAY, $feedLink->data_type);
        $this->assertEquals(PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM, $feedLink->item_node);
        $this->assertEquals('(.*) Review', $feedLink->title_match_rule_pattern);

        $response->assertStatus(302);
    }

    /**
     * The screen exists to onboard a feed, so the site's import method is set rather than
     * offered. A site left on the wrong method is the sort of thing that is only noticed when
     * something downstream quietly does not happen.
     */
    public function testANewSiteIsMarkedAsImportingByFeed()
    {
        $this->post('/staff/reviews/feed-links/probe/create', $this->validPayload());

        $site = ReviewSite::where('website_url', 'https://'.self::MARKER.'/')->first();

        $this->assertEquals(ReviewSite::REVIEW_IMPORT_BY_FEED, $site->review_import_method);
    }

    /**
     * Lands on the title rule tester rather than the index: the probe can only say a rule
     * matches the titles, not that those titles find games.
     */
    public function testRedirectsToTheTitleRuleTesterForTheNewFeedLink()
    {
        $response = $this->post('/staff/reviews/feed-links/probe/create', $this->validPayload());

        $feedLink = PartnerFeedLink::where('feed_url', $this->feedUrl())->first();

        $response->assertRedirect('/staff/reviews/feed-links/test-title-rule/'.$feedLink->id.'?source=live');
    }

    public function testAddingAFeedToAnExistingSiteCreatesNoNewSite()
    {
        $existing = ReviewSite::first();
        $this->assertNotNull($existing, 'Needs at least one review site in the database');

        $siteCountBefore = ReviewSite::count();

        $this->post('/staff/reviews/feed-links/probe/create', $this->validPayload([
            'site_mode' => 'existing',
            'site_id' => $existing->id,
        ]));

        $this->assertEquals($siteCountBefore, ReviewSite::count());

        $feedLink = PartnerFeedLink::where('feed_url', $this->feedUrl())->first();
        $this->assertNotNull($feedLink);
        $this->assertEquals($existing->id, $feedLink->site_id);
    }

    /**
     * Bad feed link details must not leave a review site behind. A site with no feed link
     * looks like a manual-submission partner and sits in the list looking finished, which is
     * a worse state to find later than an outright failure.
     *
     * Note what this does and does not prove. Validation runs before the transaction opens,
     * so this case never reaches it - the site is not created because nothing is. The
     * transaction guards a different case: a database-level failure on the feed link insert
     * after the site has been written. That case could not be provoked through the HTTP layer
     * (there is no unique constraint to violate, and MySQL coerces bad types rather than
     * rejecting them), so it stays uncovered and the transaction is defence in depth.
     */
    public function testRejectedFeedLinkDetailsLeaveNoOrphanedSite()
    {
        $response = $this->post(
            '/staff/reviews/feed-links/probe/create',
            $this->validPayload(['item_node' => ''])
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors('item_node');

        $this->assertNull(
            ReviewSite::where('website_url', 'https://'.self::MARKER.'/')->first(),
            'A review site was created despite the feed link being rejected'
        );
    }

    public function testTheExistingSiteModeStillRequiresASite()
    {
        $response = $this->post('/staff/reviews/feed-links/probe/create', $this->validPayload([
            'site_mode' => 'existing',
            'site_id' => '',
        ]));

        $response->assertSessionHasErrors('site_id');

        $this->assertNull(PartnerFeedLink::where('feed_url', $this->feedUrl())->first());
    }
}
