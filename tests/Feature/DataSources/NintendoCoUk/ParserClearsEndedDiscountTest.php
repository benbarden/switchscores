<?php

namespace Tests\Feature\DataSources\NintendoCoUk;

use App\Models\DataSourceParsed;
use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Regression cover for discounts that outlived the sale that created them.
 *
 * parseItem() used to write the discount fields only when they were non-null, so when a sale
 * ended the parser computed null, skipped the write, and the old discount stayed on the record
 * indefinitely. Found on game 17832 (Isekai Villain Nintendo Switch 2 Edition), which showed a
 * 10% discount the eShop no longer offered.
 */
class ParserClearsEndedDiscountTest extends TestCase
{
    use DatabaseTransactions;

    const SOURCE_ID = 2;
    const LINK_ID = '999000111';

    private function makeRaw(array $priceFields): DataSourceRaw
    {
        return new DataSourceRaw([
            'source_id' => self::SOURCE_ID,
            'console_id' => 2,
            'link_id' => self::LINK_ID,
            'title' => 'Discount Test Game',
            'source_data_json' => json_encode($priceFields),
        ]);
    }

    private function makeExistingParsedWithDiscount(): DataSourceParsed
    {
        return DataSourceParsed::create([
            'source_id' => self::SOURCE_ID,
            'console_id' => 2,
            'link_id' => self::LINK_ID,
            'title' => 'Discount Test Game',
            'price_standard' => '0.89',
            'price_discounted' => '0.89',
            'price_discount_pc' => '10.0',
        ]);
    }

    public function testEndedDiscountIsClearedFromExistingRecord()
    {
        $this->makeExistingParsedWithDiscount();

        // The API's no-discount shape: percentage of 0, all price fields agreeing.
        $raw = $this->makeRaw([
            'price_regular_f' => 0.89,
            'price_lowest_f' => 0.89,
            'price_sorting_f' => 0.89,
            'price_discount_percentage_f' => 0,
            'price_has_discount_b' => false,
        ]);

        $parser = new Parser();
        $parser->setDataSourceRaw($raw);
        $parsed = $parser->parseItem();

        $this->assertNull($parsed->price_discounted, 'Ended sale should clear price_discounted');
        $this->assertNull($parsed->price_discount_pc, 'Ended sale should clear price_discount_pc');
        $this->assertEquals('0.89', $parsed->price_standard, 'Standard price should be untouched');
    }

    public function testLiveDiscountIsStillRecorded()
    {
        $raw = $this->makeRaw([
            'price_regular_f' => 44.99,
            'price_lowest_f' => 22.49,
            'price_sorting_f' => 22.49,
            'price_discount_percentage_f' => 50,
            'price_has_discount_b' => true,
        ]);

        $parser = new Parser();
        $parser->setDataSourceRaw($raw);
        $parsed = $parser->parseItem();

        $this->assertEquals('22.49', $parsed->price_discounted);
        $this->assertEquals(50, $parsed->price_discount_pc);
    }

    public function testDiscountEndingDoesNotDisturbAnExistingDiscountThatIsStillLive()
    {
        $this->makeExistingParsedWithDiscount();

        $raw = $this->makeRaw([
            'price_regular_f' => 10.00,
            'price_lowest_f' => 5.00,
            'price_sorting_f' => 5.00,
            'price_discount_percentage_f' => 50,
            'price_has_discount_b' => true,
        ]);

        $parser = new Parser();
        $parser->setDataSourceRaw($raw);
        $parsed = $parser->parseItem();

        $this->assertEquals('5.00', $parsed->price_discounted);
        $this->assertEquals(50, $parsed->price_discount_pc);
    }
}
