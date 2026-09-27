<?php

namespace Tests\Unit\Domain\ReviewDraft;

use App\Domain\Game\Repository as RepoGame;
use App\Domain\ReviewDraft\ImportByFeed;
use App\Models\PartnerFeedLink;
use App\Models\ReviewSite;
use Psr\Log\NullLogger;

use Tests\TestCase;

/**
 * Covers what an import run records on the feed link, and which feeds it will load at all.
 *
 * Before this, a feed that could not be loaded never recorded a failure, and nothing ever set a
 * feed back to successful - so was_last_run_successful was a leftover from old code in both
 * directions. The network is stood in for by overriding loadItems(), and the feed link's save()
 * is stubbed, so none of this touches a real feed or the database.
 */
class ImportByFeedRunStatusTest extends TestCase
{
    public function testArchivedFeedIsNeverLoaded()
    {
        $feedLink = $this->makeFeedLink(PartnerFeedLink::FEED_STATUS_ARCHIVED);
        $import = $this->makeImport($feedLink, fn() => []);

        $import->runImport();

        $this->assertEquals(0, $import->loadCount);
        $this->assertEquals(0, $feedLink->saveCount);
    }

    public function testBrokenFeedIsNeverLoaded()
    {
        $feedLink = $this->makeFeedLink(PartnerFeedLink::FEED_STATUS_BROKEN);
        $import = $this->makeImport($feedLink, fn() => []);

        $import->runImport();

        $this->assertEquals(0, $import->loadCount);
        $this->assertEquals(0, $feedLink->saveCount);
    }

    public function testTestStatusFeedIsNotImported()
    {
        $feedLink = $this->makeFeedLink(PartnerFeedLink::FEED_STATUS_TEST);
        $import = $this->makeImport($feedLink, fn() => []);

        $import->runImport();

        $this->assertEquals(0, $import->loadCount);
        $this->assertEquals(0, $feedLink->saveCount);
    }

    public function testLoadFailureIsRecordedAndRethrown()
    {
        $feedLink = $this->makeFeedLink(PartnerFeedLink::FEED_STATUS_LIVE);
        $feedLink->was_last_run_successful = 1;
        $import = $this->makeImport($feedLink, function () {
            throw new \Exception('HTTP 403');
        });

        try {
            $import->runImport();
            $this->fail('Expected the load failure to be rethrown');
        } catch (\Exception $e) {
            $this->assertEquals('HTTP 403', $e->getMessage());
        }

        $this->assertEquals(0, $feedLink->was_last_run_successful);
        $this->assertEquals('HTTP 403', $feedLink->last_run_status);
        $this->assertNotNull($feedLink->last_run_at);
        $this->assertNull($feedLink->last_successful_run_at);
    }

    public function testSuccessfulRunIsRecordedWithCounts()
    {
        $feedLink = $this->makeFeedLink(PartnerFeedLink::FEED_STATUS_LIVE);
        $import = $this->makeImport($feedLink, fn() => []);

        $import->runImport();

        $this->assertEquals(1, $import->loadCount);
        $this->assertEquals(1, $feedLink->was_last_run_successful);
        $this->assertEquals('Imported: 0 - Skipped: 0', $feedLink->last_run_status);
        $this->assertNotNull($feedLink->last_run_at);
        $this->assertNotNull($feedLink->last_successful_run_at);
    }

    public function testSuccessAfterFailureClearsTheFailedState()
    {
        $feedLink = $this->makeFeedLink(PartnerFeedLink::FEED_STATUS_LIVE);

        $failing = $this->makeImport($feedLink, function () {
            throw new \Exception('HTTP 500');
        });
        try {
            $failing->runImport();
        } catch (\Exception $e) {
            // expected
        }
        $this->assertEquals(0, $feedLink->was_last_run_successful);

        $working = $this->makeImport($feedLink, fn() => []);
        $working->runImport();

        $this->assertEquals(1, $feedLink->was_last_run_successful);
        $this->assertEquals('Imported: 0 - Skipped: 0', $feedLink->last_run_status);
        $this->assertNotNull($feedLink->last_successful_run_at);
    }

    private function makeFeedLink($feedStatus)
    {
        $feedLink = new class extends PartnerFeedLink {
            public $saveCount = 0;

            public function save(array $options = [])
            {
                $this->saveCount++;
                return true;
            }
        };
        $feedLink->id = 999;
        $feedLink->site_id = 1;
        $feedLink->feed_status = $feedStatus;
        $feedLink->feed_url = 'https://example.com/feed';

        return $feedLink;
    }

    /**
     * An ImportByFeed whose loadItems() runs $loader instead of fetching, and counts its calls.
     */
    private function makeImport(PartnerFeedLink $feedLink, callable $loader)
    {
        $import = new class(resolve(RepoGame::class)) extends ImportByFeed {
            public $loadCount = 0;
            public $loader;

            protected function loadItems($feedUrl)
            {
                $this->loadCount++;
                return ($this->loader)();
            }
        };
        $import->loader = $loader;
        $import->setLogger(new NullLogger());
        $import->setFeedLink($feedLink);

        $site = new ReviewSite();
        $site->id = 1;
        $site->name = 'Test site';
        $prop = new \ReflectionProperty(ImportByFeed::class, 'reviewSite');
        $prop->setAccessible(true);
        $prop->setValue($import, $site);

        return $import;
    }
}
