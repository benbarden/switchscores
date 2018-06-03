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

    public function testGetDatesUnreleased()
    {
        $dates = 'Unreleased';

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('Unreleased', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }

    public function testGetDatesTBA()
    {
        $dates = 'TBA';

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('TBA', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }

    public function testGetDatesQ4()
    {
        $dates = [
            0 => '000000002018-12-31-0000',
            1 => 'Q4 2018',
        ];

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('2018-Q4', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }

    public function testGetDates2018()
    {
        $dates = [
            0 => '000000002018-13-31-0000',
            1 => '2018',
        ];

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('2018-XX', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }

    public function testGetDatesBasic1()
    {
        $dates = [
            0 => '000000002018-04-26-0000',
            1 => 'April 26, 2018',
        ];

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals('2018-04-26', $releaseDate);
        $this->assertEquals('2018-04-26', $upcomingDate);
        $this->assertEquals('1', $isReleased);
    }

    public function testGetDatesBasic2()
    {
        $dates = [
            0 => '000000002018-02-13-0000',
            1 => 'February 13, 2018',
        ];

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals('2018-02-13', $releaseDate);
        $this->assertEquals('2018-02-13', $upcomingDate);
        $this->assertEquals('1', $isReleased);
    }

    public function testGetDatesBasic3()
    {
        $dates = [
            0 => '000000002018-02-16-0000',
            1 => 'February 16, 2018',
        ];

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals('2018-02-16', $releaseDate);
        $this->assertEquals('2018-02-16', $upcomingDate);
        $this->assertEquals('1', $isReleased);
    }
}
