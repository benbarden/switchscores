<?php

namespace Tests\Unit\Domain\WeeklyBatch;

use App\Domain\WeeklyBatch\ParseService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Which pasted items count as in range for a batch. Upcoming has no upper limit (#193).
 */
class ParseServiceDateRangeTest extends TestCase
{
    private ParseService $parseService;

    public function setUp(): void
    {
        parent::setUp();
        $this->parseService = $this->app->make(ParseService::class);
    }

    public function tearDown(): void
    {
        unset($this->parseService);
        parent::tearDown();
    }

    public function testNewIsTheWeekUpToTheBatchDate()
    {
        $this->assertSame(['2026-10-17', '2026-10-23'], $this->parseService->getDateRange('2026-10-23', 'new'));
    }

    public function testUpcomingHasNoUpperLimit()
    {
        $this->assertSame(['2026-10-23', null], $this->parseService->getDateRange('2026-10-23', 'upcoming'));
    }

    public function testUpcomingTakesGamesMonthsAhead()
    {
        [$from, $to] = $this->parseService->getDateRange('2026-10-23', 'upcoming');

        $this->assertTrue($this->parseService->isInRange('2026-10-23', $from, $to));
        $this->assertTrue($this->parseService->isInRange('2027-03-19', $from, $to));
        $this->assertFalse($this->parseService->isInRange('2026-10-22', $from, $to));
    }

    public function testUndatedItemsAreNeverInRange()
    {
        $this->assertFalse($this->parseService->isInRange(null, '2026-10-23', null));
        $this->assertFalse($this->parseService->isInRange('', '2026-10-17', '2026-10-23'));
    }

    public function testNewKeepsItsUpperLimit()
    {
        [$from, $to] = $this->parseService->getDateRange('2026-10-23', 'new');

        $this->assertTrue($this->parseService->isInRange('2026-10-17', $from, $to));
        $this->assertFalse($this->parseService->isInRange('2026-10-24', $from, $to));
    }

    public function testAcceptsDates()
    {
        // The confirm screen passes the item's cast release date
        $this->assertTrue($this->parseService->isInRange(Carbon::parse('2026-12-01'), '2026-10-23', null));
    }
}
