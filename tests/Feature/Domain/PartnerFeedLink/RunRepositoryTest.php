<?php

namespace Tests\Feature\Domain\PartnerFeedLink;

use App\Domain\Feed\FeedRunOutcome;
use App\Domain\PartnerFeedLink\RunRepository;
use App\Models\PartnerFeedLink;
use App\Models\PartnerFeedLinkRun;
use App\Models\ReviewSite;
use Carbon\Carbon;

use Tests\TestCase;

/**
 * Run history is kept to the newest RunRepository::KEEP_RUNS per feed link.
 *
 * Writes to the development database. The feed link carries the marker below and is removed in
 * setUp and tearDown; its runs go with it (the foreign key cascades).
 */
class RunRepositoryTest extends TestCase
{
    const MARKER = 'run-repository-test.invalid';

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
            'feed_status' => PartnerFeedLink::FEED_STATUS_TEST,
            'site_id' => $site->id,
            'feed_url' => 'https://'.self::MARKER.'/feed',
            'data_type' => PartnerFeedLink::DATA_TYPE_ARRAY,
            'item_node' => PartnerFeedLink::ITEM_NODE_CHANNEL_ITEM,
        ]);
    }

    public function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    private function cleanUp()
    {
        PartnerFeedLink::where('feed_url', 'like', '%'.self::MARKER.'%')->delete();
    }

    private function runValues(Carbon $at, $outcome = FeedRunOutcome::OK)
    {
        return [
            'run_at' => $at,
            'was_successful' => $outcome === FeedRunOutcome::OK,
            'outcome' => $outcome,
            'message' => 'Test run',
        ];
    }

    public function testOnlyTheNewestRunsAreKept()
    {
        $repo = new RunRepository();
        $start = Carbon::parse('2026-10-01 04:30:00');

        for ($i = 0; $i < RunRepository::KEEP_RUNS + 5; $i++) {
            $repo->record($this->feedLink->id, $this->runValues($start->copy()->addDays($i)));
        }

        $runs = $repo->getForFeedLink($this->feedLink->id);

        $this->assertCount(RunRepository::KEEP_RUNS, $runs);
        // Newest first, and the five oldest are the ones that went.
        $this->assertEquals($start->copy()->addDays(RunRepository::KEEP_RUNS + 4), $runs->first()->run_at);
        $this->assertEquals($start->copy()->addDays(5), $runs->last()->run_at);
    }

    public function testPruningOneFeedLeavesOthersAlone()
    {
        $other = PartnerFeedLinkRun::where('feed_link_id', '!=', $this->feedLink->id)->count();

        $repo = new RunRepository();
        for ($i = 0; $i < RunRepository::KEEP_RUNS + 2; $i++) {
            $repo->record($this->feedLink->id, $this->runValues(Carbon::now()->subMinutes($i)));
        }

        $this->assertEquals($other, PartnerFeedLinkRun::where('feed_link_id', '!=', $this->feedLink->id)->count());
    }

    public function testRunsAreRemovedWithTheirFeedLink()
    {
        (new RunRepository())->record($this->feedLink->id, $this->runValues(Carbon::now(), FeedRunOutcome::BLOCKED_BITNINJA));
        $feedLinkId = $this->feedLink->id;

        $this->feedLink->delete();

        $this->assertEquals(0, PartnerFeedLinkRun::where('feed_link_id', $feedLinkId)->count());
    }
}
