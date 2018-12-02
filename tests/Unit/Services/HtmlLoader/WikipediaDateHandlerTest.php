<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Services\HtmlLoader\Wikipedia\DateHandler;
use Illuminate\Support\Collection;
use Tests\TestCase;

use App\Game;
use App\GameReleaseDate;
use App\FeedItemGame;

class WikipediaDateHandlerTest extends TestCase
{
    /**
     * @var DateHandler
     */
    private $dateHandler;

    public function setUp()
    {
        $this->dateHandler = new DateHandler();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->dateHandler);

        parent::tearDown();
    }

    public function testGetYearsArray()
    {
        $expected = ['2017', '2018', '2019', '2020'];

        $this->assertEquals($expected, $this->dateHandler->getYearsArray());
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

        $this->assertEquals($expected, $this->dateHandler->getYearMonthsArray());
    }

    public function testGetYearQuartersArray()
    {
        $expected = [
            'Q1 2017', 'Q2 2017', 'Q3 2017', 'Q4 2017',
            'Q1 2018', 'Q2 2018', 'Q3 2018', 'Q4 2018',
            'Q1 2019', 'Q2 2019', 'Q3 2019', 'Q4 2019',
            'Q1 2020', 'Q2 2020', 'Q3 2020', 'Q4 2020',
        ];

        $this->assertEquals($expected, $this->dateHandler->getYearQuartersArray());
    }

    // *** isYear *** //

    public function testIsYear2019()
    {
        $releaseDateRaw = '2019';
        $this->assertEquals(true, $this->dateHandler->isYear($releaseDateRaw));
    }

    public function testIsYear2018()
    {
        $releaseDateRaw = '2018';
        $this->assertEquals(true, $this->dateHandler->isYear($releaseDateRaw));
    }

    public function testIsYearNotQuarter()
    {
        $releaseDateRaw = 'Q1 2018';
        $this->assertEquals(false, $this->dateHandler->isYear($releaseDateRaw));
    }

    public function testIsYearNotMonth()
    {
        $releaseDateRaw = 'September 2019';
        $this->assertEquals(false, $this->dateHandler->isYear($releaseDateRaw));
    }

    public function testIsYearNotDate()
    {
        $releaseDateRaw = 'September 15, 2019';
        $this->assertEquals(false, $this->dateHandler->isYear($releaseDateRaw));
    }

    // *** isYearXX *** //

    public function testIsYear2019XX()
    {
        $releaseDateRaw = '2019-XX';
        $this->assertEquals(true, $this->dateHandler->isYearXX($releaseDateRaw));
    }

    // *** isQuarterYear *** //

    public function testIsQuarter1()
    {
        $releaseDateRaw = 'Q1 2018';
        $this->assertEquals(true, $this->dateHandler->isQuarterYear($releaseDateRaw));
    }

    public function testIsQuarter2()
    {
        $releaseDateRaw = 'Q2 2018';
        $this->assertEquals(true, $this->dateHandler->isQuarterYear($releaseDateRaw));
    }

    public function testIsQuarterNotYear2018()
    {
        $releaseDateRaw = '2018';
        $this->assertEquals(false, $this->dateHandler->isQuarterYear($releaseDateRaw));
    }

    public function testIsQuarterNotYear2019()
    {
        $releaseDateRaw = '2019';
        $this->assertEquals(false, $this->dateHandler->isQuarterYear($releaseDateRaw));
    }

    public function testIsQuarterNotMonth()
    {
        $releaseDateRaw = 'September 2019';
        $this->assertEquals(false, $this->dateHandler->isQuarterYear($releaseDateRaw));
    }

    public function testIsQuarterNotDate()
    {
        $releaseDateRaw = 'September 15, 2019';
        $this->assertEquals(false, $this->dateHandler->isQuarterYear($releaseDateRaw));
    }

    // *** isQuarterYearXX *** //

    public function testIsQuarterYearXX()
    {
        $releaseDateRaw = '2019-Q3';
        $this->assertEquals(true, $this->dateHandler->isQuarterYearXX($releaseDateRaw));
    }

    // *** isMonthYear *** //

    public function testIsMonthSeptember()
    {
        $releaseDateRaw = 'September 2019';
        $this->assertEquals(true, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    public function testIsMonthNovember()
    {
        $releaseDateRaw = 'November 2020';
        $this->assertEquals(true, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    public function testIsMonthNotQuarter1()
    {
        $releaseDateRaw = 'Q1 2018';
        $this->assertEquals(false, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    public function testIsMonthNotQuarter2()
    {
        $releaseDateRaw = 'Q2 2018';
        $this->assertEquals(false, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    public function testIsMonthNotYear2018()
    {
        $releaseDateRaw = '2018';
        $this->assertEquals(false, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    public function testIsMonthNotYear2019()
    {
        $releaseDateRaw = '2019';
        $this->assertEquals(false, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    public function testIsMonthNotDate()
    {
        $releaseDateRaw = 'September 15, 2019';
        $this->assertEquals(false, $this->dateHandler->isMonthYear($releaseDateRaw));
    }

    // *** getDates *** //

    public function testGetDatesUnreleased()
    {
        $dates = 'Unreleased';

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('Unreleased', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }

    public function testGetDatesTBA()
    {
        $dates = 'TBA';

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
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

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
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

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
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

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
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

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
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

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
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

        list($releaseDate, $upcomingDate, $isReleased) = $this->dateHandler->getDates($dates);
        $this->assertEquals(null, $releaseDate);
        $this->assertEquals('2018-06-XX', $upcomingDate);
        $this->assertEquals('0', $isReleased);
    }

    public function testGetUpcomingDate2018XX()
    {
        $releaseDateRaw = '2018';
        $this->assertEquals('2018-XX', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDate2019XX()
    {
        $releaseDateRaw = '2019';
        $this->assertEquals('2019-XX', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDate2019Q1()
    {
        $releaseDateRaw = 'Q1 2019';
        $this->assertEquals('2019-Q1', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDate2019Q2()
    {
        $releaseDateRaw = 'Q2 2019';
        $this->assertEquals('2019-Q2', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDate2019Month6()
    {
        $releaseDateRaw = 'June 2019';
        $this->assertEquals('2019-06-XX', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDate2019Month8()
    {
        $releaseDateRaw = 'August 2019';
        $this->assertEquals('2019-08-XX', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDateUnreleased()
    {
        $releaseDateRaw = 'Unreleased';
        $this->assertEquals($releaseDateRaw, $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDateTBA()
    {
        $releaseDateRaw = 'TBA';
        $this->assertEquals($releaseDateRaw, $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDateValid()
    {
        $releaseDateRaw = 'December 31, 2018';
        $this->assertEquals(null, $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    // *** Testing YYMMDD *** //

    public function testGetUpcomingDateYYMMDD()
    {
        $releaseDateRaw = '2018-03-29';

        $this->assertFalse(in_array($releaseDateRaw, $this->dateHandler->getYearsArray(), true));
        $this->assertFalse($this->dateHandler->isYear($releaseDateRaw));
        $this->assertFalse($this->dateHandler->isQuarterYear($releaseDateRaw));
        $this->assertFalse($this->dateHandler->isMonthYear($releaseDateRaw));

        $this->assertEquals(null, $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

    public function testGetUpcomingDateXX()
    {
        $releaseDateRaw = '2019-XX';

        $this->assertFalse(in_array($releaseDateRaw, $this->dateHandler->getYearsArray(), true));
        $this->assertFalse($this->dateHandler->isYear($releaseDateRaw));
        $this->assertFalse($this->dateHandler->isQuarterYear($releaseDateRaw));
        $this->assertFalse($this->dateHandler->isMonthYear($releaseDateRaw));

        $this->assertEquals('2019-XX', $this->dateHandler->getUpcomingDate($releaseDateRaw));
    }

}
