<?php

namespace Tests\Unit\Services\DataSources\NintendoCoUk;

use App\Services\DataSources\NintendoCoUk\Parser;

use Tests\TestCase;

class LoaderEuropeTest extends TestCase
{
    /**
     * @var Parser
     */
    private $parser;

    public function setUp(): void
    {
        $this->parser = new Parser();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->parser);

        parent::tearDown();
    }

    public function testParsePriceNull()
    {
        $data = [];
        $expected = null;

        $this->assertEquals($expected, $this->parser->parsePrice($data));
    }

    public function testParsePriceRegular()
    {
        $data = ['price_regular_f' => '11.79'];
        $expected = '11.79';

        $this->assertEquals($expected, $this->parser->parsePrice($data));
    }

    public function testParsePriceLowest()
    {
        $data = ['price_lowest_f' => '34.99'];
        $expected = '34.99';

        $this->assertEquals($expected, $this->parser->parsePrice($data));
    }

    public function testParsePriceLowestWithDiscount()
    {
        $data = ['price_lowest_f' => '13.79', 'price_discount_percentage_f' => '15.00'];
        $expected = null;

        $this->assertEquals($expected, $this->parser->parsePrice($data));
    }

    public function testParsePriceRegularWithDiscount()
    {
        $data = ['price_regular_f' => '13.79', 'price_discount_percentage_f' => '15.00'];
        $expected = '13.79';

        $this->assertEquals($expected, $this->parser->parsePrice($data));
    }

    public function testParseReleaseDateNull()
    {
        $data = [];
        $expected = null;

        $this->assertEquals($expected, $this->parser->parseReleaseDate($data));
    }

    public function testParseReleaseDateReal()
    {
        $data = ['pretty_date_s' => '07/08/2020'];
        $expected = '2020-08-07';

        $this->assertEquals($expected, $this->parser->parseReleaseDate($data));
    }

    public function testParseReleaseDateYearOnly()
    {
        $data = ['pretty_date_s' => '2020'];
        $expected = null;

        $this->assertEquals($expected, $this->parser->parseReleaseDate($data));
    }

    public function testParseReleaseDateInvalidData()
    {
        $data = ['pretty_date_s' => 'December 2017'];
        $expected = null;

        $this->assertEquals($expected, $this->parser->parseReleaseDate($data));
    }
}
