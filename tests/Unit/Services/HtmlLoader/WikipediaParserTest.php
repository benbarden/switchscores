<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Services\HtmlLoader\Wikipedia\Parser as WikiParser;
use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

class WikipediaParserTest extends TestCase
{
    /**
     * @var WikiParser
     */
    private $wikiParser;

    public function setUp()
    {
        $this->wikiParser = new WikiParser();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->wikiParser);

        parent::tearDown();
    }

    public function testLimitField()
    {
        $input = 'abcdefghijklmnopqrstuvwxyz';

        $expected = 'abcdefghij';
        $length = 10;
        $this->assertEquals($expected, $this->wikiParser->limitField($input, $length));

        $expected = 'abcdefghijklmnopqrstuvwxy';
        $length = 25;
        $this->assertEquals($expected, $this->wikiParser->limitField($input, $length));

        $expected = 'abcdefghijklmnopqrstuvwxyz';
        $length = 26;
        $this->assertEquals($expected, $this->wikiParser->limitField($input, $length));

        $expected = 'abcdefghijklmnopqrstuvwxyz';
        $length = 27;
        $this->assertEquals($expected, $this->wikiParser->limitField($input, $length));
    }

    public function testFlattenArray()
    {
        $input = 'Bob';
        $expected = 'Bob';
        $this->assertEquals($expected, $this->wikiParser->flattenArray($input));

        $input = ['Bill', 'Ben'];
        $expected = 'Bill, Ben';
        $this->assertEquals($expected, $this->wikiParser->flattenArray($input));
    }

}
