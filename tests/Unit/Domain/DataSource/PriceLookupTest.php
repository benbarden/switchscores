<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\NsuidPrice;
use App\Domain\DataSource\NintendoCoUk\PriceLookup;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * PriceLookup reads the unofficial Nintendo eShop price API.
 *
 * The fixtures here are REAL payloads captured from the live endpoint on 2026-07-21,
 * not invented shapes - including the contaminated case documented in
 * docs/tasks/switch-2-upgrade-pack-pricing.md, where the search payload's
 * price_regular_f held the upgrade pack's £4.99 instead of the game's £24.99.
 */
class PriceLookupTest extends TestCase
{
    private function lookup(): PriceLookup
    {
        // No real sleeping between batches in tests.
        return new PriceLookup(sleeper: fn () => null);
    }

    private function priceItem(string $nsuid, string $regular): array
    {
        return [
            'title_id'      => (int) $nsuid,
            'sales_status'  => 'onsale',
            'regular_price' => ['amount' => '£'.$regular, 'currency' => 'GBP', 'raw_value' => $regular],
        ];
    }

    public function testReadsRegularPrice()
    {
        Http::fake([
            '*' => Http::response(['prices' => [$this->priceItem('70010000121555', '13.49')]]),
        ]);

        $result = $this->lookup()->fetch(['70010000121555']);
        $price = $result->get('70010000121555');

        $this->assertNotNull($price);
        $this->assertEquals('13.49', $price->regularPrice);
        $this->assertEquals(1, $result->resolved());
    }

    /**
     * title_id comes back as a JSON number, so json_decode hands us an int. NSUIDs are
     * identifiers, not quantities, and are used as array keys - so they must be strings.
     */
    public function testNsuidIsAStringEvenThoughTheApiSendsANumber()
    {
        Http::fake([
            '*' => Http::response(['prices' => [$this->priceItem('70010000121555', '13.49')]]),
        ]);

        $price = $this->lookup()->fetch(['70010000121555'])->get('70010000121555');

        $this->assertNotNull($price);
        $this->assertSame('70010000121555', $price->nsuid);
    }

    public function testReadsDiscountWithStartAndEndDates()
    {
        Http::fake([
            '*' => Http::response(['prices' => [[
                'title_id'       => 70010000111806,
                'sales_status'   => 'onsale',
                'regular_price'  => ['amount' => '£24.99', 'currency' => 'GBP', 'raw_value' => '24.99'],
                'discount_price' => [
                    'amount'         => '£14.99',
                    'currency'       => 'GBP',
                    'raw_value'      => '14.99',
                    'start_datetime' => '2026-07-07T13:00:00Z',
                    'end_datetime'   => '2026-08-04T22:59:59Z',
                ],
            ]]]),
        ]);

        $price = $this->lookup()->fetch(['70010000111806'])->get('70010000111806');

        $this->assertTrue($price->hasDiscount());
        $this->assertEquals('24.99', $price->regularPrice);
        $this->assertEquals('14.99', $price->discountPrice);
        $this->assertEquals('2026-08-04T22:59:59Z', $price->discountEnd);
    }

    /**
     * THE TRAP. sales_status "onsale" means PURCHASABLE, not DISCOUNTED. Nearly every
     * live SKU is "onsale" regardless of whether it has a discount, so reading that
     * field as the discount signal would put a sale badge on the whole catalogue -
     * the exact user-facing bug (thousands of phantom discounts) that was fixed on
     * 2026-07-20. The discount signal is the presence of discount_price.
     */
    public function testOnSaleStatusWithoutADiscountBlockIsNotADiscount()
    {
        Http::fake([
            '*' => Http::response(['prices' => [$this->priceItem('70010000121555', '13.49')]]),
        ]);

        $price = $this->lookup()->fetch(['70010000121555'])->get('70010000121555');

        $this->assertEquals(NsuidPrice::STATUS_ON_SALE, $price->salesStatus);
        $this->assertFalse($price->hasDiscount());
        $this->assertNull($price->discountPrice);
    }

    /**
     * An unknown NSUID is returned in the response with sales_status not_found and no
     * regular_price, rather than being silently omitted. It must not look like a
     * usable price.
     */
    public function testNotFoundNsuidIsReportedAsAbsentAndCounted()
    {
        Http::fake([
            '*' => Http::response(['prices' => [
                $this->priceItem('70010000121555', '13.49'),
                ['title_id' => 70010000999999, 'sales_status' => 'not_found'],
            ]]),
        ]);

        $result = $this->lookup()->fetch(['70010000121555', '70010000999999']);

        $this->assertNull($result->get('70010000999999'));
        $this->assertNotNull($result->get('70010000121555'));
        $this->assertEquals(1, $result->notFound());
        $this->assertEquals(1, $result->resolved());
    }

    public function testBatchesAtFiftyIdsPerCall()
    {
        Http::fake(['*' => Http::response(['prices' => []])]);

        $nsuids = [];
        for ($i = 0; $i < 101; $i++) {
            $nsuids[] = '7001000000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
        }

        $result = $this->lookup()->fetch($nsuids);

        // 101 unique ids -> 50 + 50 + 1
        $this->assertEquals(3, $result->calls());
        Http::assertSentCount(3);
    }

    public function testDeduplicatesAndIgnoresEmptyNsuids()
    {
        Http::fake(['*' => Http::response(['prices' => []])]);

        $result = $this->lookup()->fetch([
            '70010000121555', '70010000121555', '', '  ', null, '70010000111806',
        ]);

        $this->assertEquals(2, $result->requested);
        Http::assertSentCount(1);
    }

    public function testNoCallIsMadeForAnEmptyNsuidList()
    {
        Http::fake();

        $result = $this->lookup()->fetch([]);

        $this->assertEquals(0, $result->calls());
        Http::assertNothingSent();
    }

    /**
     * The endpoint is unofficial and has no SLA. A failure must never break the import -
     * the caller falls back to the scalar price fields - but it must be counted, or a
     * degrading endpoint silently reverts prices to being wrong with nothing on screen.
     */
    public function testHttpErrorIsCountedAndDoesNotThrow()
    {
        Http::fake(['*' => Http::response('upstream error', 503)]);

        $result = $this->lookup()->fetch(['70010000121555']);

        $this->assertEquals(1, $result->failedBatches());
        $this->assertTrue($result->hasFailures());
        $this->assertNull($result->get('70010000121555'));
        $this->assertEquals(0, $result->resolved());
    }

    public function testConnectionFailureIsCountedAndDoesNotThrow()
    {
        Http::fake(function () {
            throw new \RuntimeException('Connection refused');
        });

        $result = $this->lookup()->fetch(['70010000121555']);

        $this->assertEquals(1, $result->failedBatches());
        $this->assertNull($result->get('70010000121555'));
    }

    /**
     * A 200 whose body we do not recognise means the endpoint changed shape. That is
     * NOT the same fact as "these SKUs have no price", and conflating them would let a
     * silently-changed API read as a catalogue with no prices.
     */
    public function testUnexpectedBodyShapeCountsAsAFailedBatchNotAnEmptyOne()
    {
        Http::fake(['*' => Http::response(['unexpected' => 'shape'])]);

        $result = $this->lookup()->fetch(['70010000121555']);

        $this->assertEquals(1, $result->failedBatches());
        $this->assertEquals(0, $result->resolved());
    }

    /**
     * One failing batch must not discard the batches that worked.
     */
    public function testAFailedBatchDoesNotLoseTheOtherBatches()
    {
        Http::fake(['*' => Http::sequence()
            ->push('upstream error', 503)
            ->push(['prices' => [$this->priceItem('70010000121555', '13.49')]]),
        ]);

        $nsuids = [];
        for ($i = 0; $i < 50; $i++) {
            $nsuids[] = '7001000000'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
        }
        $nsuids[] = '70010000121555';

        $result = $this->lookup()->fetch($nsuids);

        $this->assertEquals(1, $result->failedBatches());
        $this->assertEquals(2, $result->calls());
        $this->assertNotNull($result->get('70010000121555'));
    }

    /**
     * The whole point of the integration: pick the standalone game's SKU, not the
     * cheapest one. This is the Kirby / Metroid Prime 4 / Xenoblade X shape, where the
     * upgrade pack is what the scalar price fields were publishing.
     */
    public function testFindsTheStandaloneGameSkuRatherThanTheUpgradePack()
    {
        Http::fake([
            '*' => Http::response(['prices' => [
                $this->priceItem('70050000065152', '4.99'),   // upgrade pack - the wrong answer
                $this->priceItem('70010000111806', '24.99'),  // the game
            ]]),
        ]);

        $result = $this->lookup()->fetch(['70050000065152', '70010000111806']);
        $price = $result->findStandaloneGame(['70050000065152', '70010000111806']);

        $this->assertNotNull($price);
        $this->assertEquals('24.99', $price->regularPrice);
        $this->assertTrue($price->isStandaloneGame());
    }

    /**
     * Deluxe editions are the counter-example that killed the ratio heuristic: a big
     * price gap that is CORRECT. The 7001 SKU must still win.
     */
    public function testDeluxeEditionSkuDoesNotDisplaceTheStandaloneGame()
    {
        Http::fake([
            '*' => Http::response(['prices' => [
                $this->priceItem('70070000012345', '49.99'),  // deluxe edition
                $this->priceItem('70010000012345', '34.99'),  // Street Fighter 6 base
            ]]),
        ]);

        $result = $this->lookup()->fetch(['70070000012345', '70010000012345']);
        $price = $result->findStandaloneGame(['70070000012345', '70010000012345']);

        $this->assertEquals('34.99', $price->regularPrice);
    }

    /**
     * Fruit Mountain Party carries a single NSUID and no upgrade SKU at all, yet its
     * scalar price was still wrong (£2.49 against a real £13.49). The lookup must
     * handle a lone standalone SKU.
     */
    public function testASingleStandaloneSkuResolves()
    {
        Http::fake([
            '*' => Http::response(['prices' => [$this->priceItem('70010000121555', '13.49')]]),
        ]);

        $result = $this->lookup()->fetch(['70010000121555']);

        $this->assertEquals('13.49', $result->findStandaloneGame(['70010000121555'])->regularPrice);
    }

    public function testReturnsNullWhenThereIsNoStandaloneSku()
    {
        Http::fake([
            '*' => Http::response(['prices' => [$this->priceItem('70050000065152', '4.99')]]),
        ]);

        $result = $this->lookup()->fetch(['70050000065152']);

        $this->assertNull($result->findStandaloneGame(['70050000065152']));
    }
}
