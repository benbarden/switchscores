<?php

namespace Tests\Unit\Domain\StaffDashboard;

use App\Domain\StaffDashboard\TileState;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Pins the amber/red boundaries for staff dashboard queues (#163). A queue turns amber once its
 * oldest item is older than the amber threshold in whole days, not on the day it reaches it.
 */
class TileStateTest extends TestCase
{
    private Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->now = Carbon::parse('2026-09-27 12:00:00');
    }

    public function testEmptyQueueIsOk()
    {
        $this->assertEquals(TileState::OK, TileState::forAge(null, 3, 7, $this->now));
    }

    public function testExactlyAtAmberThresholdIsStillOk()
    {
        $oldest = $this->now->copy()->subDays(3);
        $this->assertEquals(TileState::OK, TileState::forAge($oldest, 3, 7, $this->now));
    }

    public function testOneDayPastAmberThresholdIsAmber()
    {
        $oldest = $this->now->copy()->subDays(4);
        $this->assertEquals(TileState::AMBER, TileState::forAge($oldest, 3, 7, $this->now));
    }

    public function testExactlyAtRedThresholdIsAmber()
    {
        $oldest = $this->now->copy()->subDays(7);
        $this->assertEquals(TileState::AMBER, TileState::forAge($oldest, 3, 7, $this->now));
    }

    public function testPastRedThresholdIsRed()
    {
        $oldest = $this->now->copy()->subDays(8);
        $this->assertEquals(TileState::RED, TileState::forAge($oldest, 3, 7, $this->now));
    }

    public function testNoRedThresholdNeverGoesRed()
    {
        $oldest = $this->now->copy()->subDays(60);
        $this->assertEquals(TileState::AMBER, TileState::forAge($oldest, 7, null, $this->now));
    }

    public function testPartDayCountsAsTheWholeDaysElapsed()
    {
        $oldest = $this->now->copy()->subDays(3)->subHours(23);
        $this->assertEquals(TileState::OK, TileState::forAge($oldest, 3, 7, $this->now));
    }

    public function testDescribeAge()
    {
        $this->assertEquals('today', TileState::describeAge($this->now->copy()->subHours(5), $this->now));
        $this->assertEquals('1 day', TileState::describeAge($this->now->copy()->subDay(), $this->now));
        $this->assertEquals('4 days', TileState::describeAge($this->now->copy()->subDays(4), $this->now));
    }

    public function testEveryStateHasAChipLabelExceptNone()
    {
        $this->assertNotEmpty(TileState::queueLabel(TileState::OK));
        $this->assertNotEmpty(TileState::queueLabel(TileState::AMBER));
        $this->assertNotEmpty(TileState::queueLabel(TileState::RED));
        $this->assertEquals('', TileState::queueLabel(TileState::NONE));
    }
}
