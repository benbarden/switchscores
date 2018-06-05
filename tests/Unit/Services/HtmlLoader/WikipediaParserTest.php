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

    public function testGetYearsArray()
    {
        $expected = [2017, 2018, 2019, 2020];

        $this->assertEquals($expected, $this->wikiParser->getYearsArray());
    }

    public function testGetYearMonthsArray()
    {
        $expected = [
            'March 2017', 'April 2017', 'May 2017', 'June 2017',
            'July 2017', 'August 2017', 'September 2017', 'October 2017', 'November 2017', 'December 2017',
            'January 2018', 'February 2018', 'March 2018', 'April 2018', 'May 2018', 'June 2018',
            'July 2018', 'August 2018', 'September 2018', 'October 2018', 'November 2018', 'December 2018',
            'January 2019', 'February 2019', 'March 2019', 'April 2019', 'May 2019', 'June 2019',
            'July 2019', 'August 2019', 'September 2019', 'October 2019', 'November 2019', 'December 2019',
            'January 2020', 'February 2020', 'March 2020', 'April 2020', 'May 2020', 'June 2020',
            'July 2020', 'August 2020', 'September 2020', 'October 2020', 'November 2020', 'December 2020',
        ];

        $this->assertEquals($expected, $this->wikiParser->getYearMonthsArray());
    }

    public function testGetYearQuartersArray()
    {
        $expected = [
            'Q1 2017', 'Q2 2017', 'Q3 2017', 'Q4 2017',
            'Q1 2018', 'Q2 2018', 'Q3 2018', 'Q4 2018',
            'Q1 2019', 'Q2 2019', 'Q3 2019', 'Q4 2019',
            'Q1 2020', 'Q2 2020', 'Q3 2020', 'Q4 2020',
        ];

        $this->assertEquals($expected, $this->wikiParser->getYearQuartersArray());
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

    public function testGetDatesMonth1()
    {
        $dates = [
            0 => '000000002018-06-01-0000',
            1 => 'June 2018',
        ];

        list($releaseDate, $upcomingDate, $isReleased) = $this->wikiParser->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('2018-06-XX', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }
}
