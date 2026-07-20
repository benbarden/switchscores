<?php

namespace Tests\Feature\DataSources\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Cover for the Switch 2 standard price during a sale.
 *
 * price_sorting_f becomes the SALE price while a Switch 2 game is discounted, so preferring it
 * (correct when not on sale, where price_regular_f can be the deluxe/upgrade price) recorded the
 * sale price as the RRP. Games showed "30% off £4.71 → £4.71" with the real £6.73 lost.
 * Found on 23 games during the 2026-07-20 stale-discount investigation.
 */
class ParserSwitch2SalePriceTest extends TestCase
{
    use DatabaseTransactions;

    const SOURCE_ID = 2;
    const CONSOLE_SWITCH_2 = 2;
    const CONSOLE_SWITCH_1 = 1;

    private function parseWith(array $priceFields, $consoleId, string $linkId, ?string $existingStandardPrice = null)
    {
        if ($existingStandardPrice !== null) {
            DataSourceParsed::create([
                'source_id' => self::SOURCE_ID,
                'console_id' => $consoleId,
                'link_id' => $linkId,
                'title' => 'Sale Price Test Game',
                'price_standard' => $existingStandardPrice,
            ]);
        }

        $raw = new DataSourceRaw([
            'source_id' => self::SOURCE_ID,
            'console_id' => $consoleId,
            'link_id' => $linkId,
            'title' => 'Sale Price Test Game',
            'source_data_json' => json_encode($priceFields),
        ]);

        $parser = new Parser();
        $parser->setDataSourceRaw($raw);
        return $parser->parseItem();
    }

    /** The real shape of game 2879690: RRP 6.73, on sale at 4.71, 30% off. */
    public function testKnownRegularPriceIsPreservedDuringASale()
    {
        $parsed = $this->parseWith([
            'price_regular_f' => 6.73,
            'price_lowest_f' => 4.71,
            'price_sorting_f' => 4.71, // the sale price — must NOT become the standard price
            'price_discount_percentage_f' => 30,
            'price_has_discount_b' => true,
        ], self::CONSOLE_SWITCH_2, '900000001', '6.73');

        $this->assertEquals('6.73', $parsed->price_standard, 'Known RRP must survive the sale');
        $this->assertEquals('4.71', $parsed->price_discounted);
        $this->assertEquals(30, $parsed->price_discount_pc);
    }

    public function testRrpIsDerivedWhenNoPriceIsOnRecord()
    {
        // A game first seen while already on sale — nothing observed to preserve.
        $parsed = $this->parseWith([
            'price_regular_f' => 10, // the upgrade-pack price, not the game's RRP
            'price_lowest_f' => 32.49,
            'price_sorting_f' => 32.49,
            'price_discount_percentage_f' => 35,
            'price_has_discount_b' => true,
        ], self::CONSOLE_SWITCH_2, '900000002');

        // 32.49 / 0.65 = 49.9846..., i.e. an RRP of about 49.99
        $this->assertEquals('49.98', $parsed->price_standard);
        $this->assertEquals('32.49', $parsed->price_discounted);
    }

    public function testSortingPriceIsStillUsedWhenNotOnSale()
    {
        // The original Switch 2 rule must be untouched outside a sale.
        $parsed = $this->parseWith([
            'price_regular_f' => 59.99, // deluxe edition
            'price_lowest_f' => 39.99,
            'price_sorting_f' => 39.99, // base edition
            'price_discount_percentage_f' => 0,
            'price_has_discount_b' => false,
        ], self::CONSOLE_SWITCH_2, '900000003');

        $this->assertEquals('39.99', $parsed->price_standard);
        $this->assertNull($parsed->price_discounted);
    }

    public function testSaleEndingRestoresTheRealPrice()
    {
        // Self-healing: once the sale ends, price_sorting_f is the full price again.
        $parsed = $this->parseWith([
            'price_regular_f' => 6.73,
            'price_lowest_f' => 6.73,
            'price_sorting_f' => 6.73,
            'price_discount_percentage_f' => 0,
            'price_has_discount_b' => false,
        ], self::CONSOLE_SWITCH_2, '900000004', '4.71'); // previously corrupted value

        $this->assertEquals('6.73', $parsed->price_standard, 'Sale end should correct a bad price');
        $this->assertNull($parsed->price_discount_pc);
    }

    public function testSwitch1IsUnaffected()
    {
        // Switch 1 uses price_regular_f, which is the true RRP and does not move during a sale.
        $parsed = $this->parseWith([
            'price_regular_f' => 29.99,
            'price_lowest_f' => 14.99,
            'price_sorting_f' => 14.99,
            'price_discount_percentage_f' => 50,
            'price_has_discount_b' => true,
        ], self::CONSOLE_SWITCH_1, '900000005', '29.99');

        $this->assertEquals('29.99', $parsed->price_standard);
        $this->assertEquals('14.99', $parsed->price_discounted);
    }

    public function testHundredPercentDiscountDoesNotDivideByZero()
    {
        $parsed = $this->parseWith([
            'price_regular_f' => 4.99,
            'price_lowest_f' => 0.01,
            'price_sorting_f' => 0.01,
            'price_discount_percentage_f' => 100,
            'price_has_discount_b' => true,
        ], self::CONSOLE_SWITCH_2, '900000006');

        // Falls through to the sorting/regular path rather than erroring.
        $this->assertNotNull($parsed->price_standard);
    }
}
