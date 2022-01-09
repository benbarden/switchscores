<?php

namespace Tests\Unit\Services\DataSources\NintendoCoUk;

use App\Models\DataSourceRaw;
use App\Services\DataSources\NintendoCoUk\Parser;
use Tests\TestCase;

class ParsePriceTest extends TestCase
{
    public function makeParser($data)
    {
        $jsonData = json_encode($data);
        $dsRaw = new DataSourceRaw(['source_id' => 1, 'title' => 'Test', 'source_data_json' => $jsonData]);
        $parser = new Parser($dsRaw);
        return $parser;
    }

    public function testParsePriceNull()
    {
        $data = [];
        $parser = $this->makeParser($data);
        $expected = [null, null, null];

        $this->assertEquals($expected, $parser->parsePrice($data));
    }

    public function testParsePriceRegular()
    {
        $data = ['price_regular_f' => '11.79'];
        $parser = $this->makeParser($data);
        $expected = ['11.79', null, null];

        $this->assertEquals($expected, $parser->parsePrice($data));
    }

    public function testParsePriceLowest()
    {
        $data = ['price_regular_f' => '34.99', 'price_lowest_f' => '34.99'];
        $parser = $this->makeParser($data);
        $expected = ['34.99', null, null];

        $this->assertEquals($expected, $parser->parsePrice($data));
    }

    public function testParsePriceNoDiscount()
    {
        $data = ['price_lowest_f' => '17.99', 'price_discount_percentage_f' => '0.0', 'price_regular_f' => '17.99', 'price_discounted_f' => null];
        $parser = $this->makeParser($data);
        $expected = ['17.99', null, null];

        $this->assertEquals($expected, $parser->parsePrice($data));
    }

    public function testParsePriceWithDiscount()
    {
        $data = ['price_lowest_f' => '12.59', 'price_discount_percentage_f' => '30.0', 'price_regular_f' => '17.99', 'price_discounted_f' => '12.59'];
        $parser = $this->makeParser($data);
        $expected = ['17.99', '12.59', '30.0'];

        $this->assertEquals($expected, $parser->parsePrice($data));
    }
}
