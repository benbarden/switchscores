<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\NsuidPrice;
use App\Domain\DataSource\NintendoCoUk\PriceLookupResult;
use App\Domain\DataSource\NintendoCoUk\PriceStore;
use App\Models\DataSourcePrice;
use App\Models\DataSourcePriceHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * PriceStore persists per-SKU prices and appends to the history table only when a
 * price actually moved.
 *
 * The history table is only worth having if it stays honest: a row must mean "this
 * price changed", so the tests here lean on the cases that would quietly fill it with
 * noise - a first sighting, an unchanged re-fetch, and 2dp formatting differences.
 */
class PriceStoreTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Synthetic NSUIDs that no real SKU uses.
     *
     * These MUST NOT be ids that appear in live data. An earlier version of this test
     * used the real NSUIDs from the task doc and passed - until DSNintendoCoUkFetchPrices
     * was run on localdev and stored those very rows, at which point "create a new row"
     * became "update an existing one" and three tests failed. The tests had been quietly
     * asserting against an empty table rather than against their own fixtures.
     *
     * They keep the 7001/7005 prefixes because the prefix is load-bearing.
     */
    private const NSUID_GAME  = '70010000000001';
    private const NSUID_OTHER = '70010000000002';

    protected function setUp(): void
    {
        parent::setUp();

        // Defensive: guarantees a known starting state regardless of what else is in
        // the table. Inside the test transaction, so it rolls back.
        DataSourcePrice::whereIn('nsuid', [self::NSUID_GAME, self::NSUID_OTHER])->delete();
        DataSourcePriceHistory::whereIn('nsuid', [self::NSUID_GAME, self::NSUID_OTHER])->delete();
    }

    private function store(): PriceStore
    {
        return new PriceStore();
    }

    private function resultWith(NsuidPrice ...$prices): PriceLookupResult
    {
        $result = new PriceLookupResult(requested: count($prices));

        foreach ($prices as $price) {
            $result->add($price);
        }

        return $result;
    }

    private function price(
        string $nsuid,
        ?string $regular,
        ?string $discount = null,
        ?string $end = null
    ): NsuidPrice {
        return new NsuidPrice(
            nsuid:         $nsuid,
            salesStatus:   NsuidPrice::STATUS_ON_SALE,
            regularPrice:  $regular,
            discountPrice: $discount,
            discountStart: $discount ? '2026-07-07T13:00:00Z' : null,
            discountEnd:   $end,
        );
    }

    public function testCreatesARowForANewNsuid()
    {
        $this->store()->store($this->resultWith($this->price(self::NSUID_GAME, '13.49')), 2);

        $row = DataSourcePrice::where('nsuid', self::NSUID_GAME)->first();

        $this->assertNotNull($row);
        $this->assertEquals('13.49', $row->regular_price);
        $this->assertEquals(2, $row->console_id);
        $this->assertNotNull($row->first_seen_at);
        $this->assertNotNull($row->fetched_at);
    }

    /**
     * A first sighting is not a change. Logging it as one would make the history table
     * useless on the first run, when every SKU is new - hundreds of rows all claiming
     * a price moved.
     */
    public function testAFirstSightingWritesNoHistory()
    {
        $outcome = $this->store()->store(
            $this->resultWith($this->price(self::NSUID_GAME, '13.49')), 2
        );

        $this->assertEquals(0, DataSourcePriceHistory::where('nsuid', self::NSUID_GAME)->count());
        $this->assertEquals(1, $outcome->created());
        $this->assertNull(DataSourcePrice::where('nsuid', self::NSUID_GAME)->first()->price_changed_at);
    }

    public function testAnUnchangedPriceWritesNoHistory()
    {
        $store = $this->store();
        $store->store($this->resultWith($this->price(self::NSUID_GAME, '13.49')), 2);
        $outcome = $store->store($this->resultWith($this->price(self::NSUID_GAME, '13.49')), 2);

        $this->assertEquals(0, DataSourcePriceHistory::where('nsuid', self::NSUID_GAME)->count());
        $this->assertEquals(1, $outcome->unchanged());
        $this->assertEquals(0, $outcome->changed());
    }

    public function testAChangedRegularPriceAppendsHistoryWithBothSides()
    {
        $store = $this->store();
        $store->store($this->resultWith($this->price(self::NSUID_GAME, '13.49')), 2);
        $outcome = $store->store($this->resultWith($this->price(self::NSUID_GAME, '17.99')), 2);

        $history = DataSourcePriceHistory::where('nsuid', self::NSUID_GAME)->first();

        $this->assertNotNull($history);
        $this->assertEquals('13.49', $history->old_regular_price);
        $this->assertEquals('17.99', $history->new_regular_price);
        $this->assertEquals(1, $outcome->changed());
        $this->assertEquals('17.99', DataSourcePrice::where('nsuid', self::NSUID_GAME)->first()->regular_price);
        $this->assertNotNull(DataSourcePrice::where('nsuid', self::NSUID_GAME)->first()->price_changed_at);
    }

    /**
     * "13.5" from the API and "13.50" out of a DECIMAL column are the same price. If
     * they compared unequal, every run would append a history row for a price that
     * never moved and the table would be worthless within a week.
     */
    public function testFormattingDifferencesDoNotCountAsAChange()
    {
        $store = $this->store();
        $store->store($this->resultWith($this->price(self::NSUID_GAME, '13.50')), 2);
        $outcome = $store->store($this->resultWith($this->price(self::NSUID_GAME, '13.5')), 2);

        $this->assertEquals(0, DataSourcePriceHistory::where('nsuid', self::NSUID_GAME)->count());
        $this->assertEquals(1, $outcome->unchanged());
    }

    public function testADiscountStartingIsAChange()
    {
        $store = $this->store();
        $store->store($this->resultWith($this->price(self::NSUID_OTHER, '24.99')), 2);
        $outcome = $store->store($this->resultWith(
            $this->price(self::NSUID_OTHER, '24.99', '14.99', '2026-08-04T22:59:59Z')
        ), 2);

        $history = DataSourcePriceHistory::where('nsuid', self::NSUID_OTHER)->first();

        $this->assertNotNull($history);
        $this->assertNull($history->old_discount_price);
        $this->assertEquals('14.99', $history->new_discount_price);
        $this->assertEquals(1, $outcome->changed());
    }

    /**
     * A discount ending is the case the whole integration exists to get right - the
     * stale-discount bug was 4,059 sale badges on games sold at full price.
     */
    public function testADiscountEndingIsAChangeAndClearsTheDiscount()
    {
        $store = $this->store();
        $store->store($this->resultWith(
            $this->price(self::NSUID_OTHER, '24.99', '14.99', '2026-08-04T22:59:59Z')
        ), 2);
        $outcome = $store->store($this->resultWith($this->price(self::NSUID_OTHER, '24.99')), 2);

        $row = DataSourcePrice::where('nsuid', self::NSUID_OTHER)->first();

        $this->assertNull($row->discount_price);
        $this->assertNull($row->discount_end_at);
        $this->assertEquals(1, $outcome->changed());
        $this->assertEquals('14.99', DataSourcePriceHistory::where('nsuid', self::NSUID_OTHER)->first()->old_discount_price);
    }

    public function testStoresDiscountWindow()
    {
        $this->store()->store($this->resultWith(
            $this->price(self::NSUID_OTHER, '24.99', '14.99', '2026-08-04T22:59:59Z')
        ), 2);

        $row = DataSourcePrice::where('nsuid', self::NSUID_OTHER)->first();

        $this->assertNotNull($row->discount_start_at);
        $this->assertNotNull($row->discount_end_at);
        $this->assertTrue($row->hasLiveDiscount());
    }

    /**
     * hasLiveDiscount() reads the end date rather than trusting that a discount is
     * present. This is what makes an ended sale knowable from the data instead of
     * waiting for the API to stop sending it.
     */
    public function testAnExpiredDiscountIsNotLive()
    {
        $this->store()->store($this->resultWith(
            $this->price(self::NSUID_OTHER, '24.99', '14.99', Carbon::now()->subDay()->toIso8601String())
        ), 2);

        $row = DataSourcePrice::where('nsuid', self::NSUID_OTHER)->first();

        $this->assertNotNull($row->discount_price);
        $this->assertFalse($row->hasLiveDiscount());
    }

    /**
     * A dead NSUID is recorded rather than skipped. Skipping it would leave the row
     * holding its last known price with a stale fetched_at, which reads as a current
     * price that simply is not there any more.
     */
    public function testNotFoundNsuidIsRecorded()
    {
        $result = $this->resultWith(new NsuidPrice(
            nsuid:       self::NSUID_OTHER,
            salesStatus: NsuidPrice::STATUS_NOT_FOUND,
        ));

        $this->store()->store($result, 2);

        $row = DataSourcePrice::where('nsuid', self::NSUID_OTHER)->first();

        $this->assertNotNull($row);
        $this->assertEquals(NsuidPrice::STATUS_NOT_FOUND, $row->sales_status);
        $this->assertNull($row->regular_price);
    }
}
