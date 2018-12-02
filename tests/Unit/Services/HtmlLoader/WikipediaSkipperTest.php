<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Services\HtmlLoader\Wikipedia\Skipper as WikiSkipper;
use App\Services\HtmlLoader\Wikipedia\DateHandler as WikiDateHandler;
use Illuminate\Support\Collection;
use Tests\TestCase;

use App\Game;
use App\GameReleaseDate;
use App\FeedItemGame;

class WikipediaSkipperTest extends TestCase
{
    /**
     * @var WikiSkipper
     */
    private $wikiSkipper;

    /**
     * @var WikiDateHandler
     */
    private $wikiDateHandler;

    public function setUp()
    {
        $this->wikiSkipper = new WikiSkipper();
        $this->wikiDateHandler = new WikiDateHandler();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->wikiSkipper);
        unset($this->wikiDateHandler);

        parent::tearDown();
    }

    public function testCountDatesTBAOrUnreleased()
    {
        $feedItemGame = new FeedItemGame();
        $feedItemGame->upcoming_date_eu = 'Unreleased';
        $feedItemGame->upcoming_date_us = 'Unreleased';
        $feedItemGame->upcoming_date_jp = 'TBA';

        $this->assertEquals(3, $this->wikiSkipper->countDatesTBAOrUnreleased($feedItemGame));
    }

    public function testGetSkipTextNull()
    {
        $title = 'Super Mario Odyssey';
        $this->assertEquals(null, $this->wikiSkipper->getSkipText($title));
    }

    public function testGetSkipTextUntitled()
    {
        $title = 'Untitled Pokemon game';
        $this->assertEquals('Untitled ', $this->wikiSkipper->getSkipText($title));
    }

    public function testGetSkipTextTentativeTitle()
    {
        $title = 'Yoshi (tentative title)';
        $this->assertEquals('(tentative title)', $this->wikiSkipper->getSkipText($title));
    }

    public function testGetSkipTextLabo()
    {
        $title = 'Nintendo Labo Toy-Con 01';
        $this->assertEquals('Nintendo Labo', $this->wikiSkipper->getSkipText($title));
    }

    public function testCountRealDatesZeroUnreleased()
    {
        $feedItemGame = new FeedItemGame();
        $feedItemGame->upcoming_date_eu = 'Unreleased';
        $feedItemGame->upcoming_date_us = 'Unreleased';
        $feedItemGame->upcoming_date_jp = 'TBA';

        $this->assertEquals(0, $this->wikiSkipper->countRealDates($feedItemGame, $this->wikiDateHandler));
    }

    public function testCountRealDatesZeroQuartersYears()
    {
        $feedItemGame = new FeedItemGame();
        $feedItemGame->upcoming_date_eu = '2019';
        $feedItemGame->upcoming_date_us = 'Q2 2018';
        $feedItemGame->upcoming_date_jp = 'September 2020';

        $this->assertEquals(0, $this->wikiSkipper->countRealDates($feedItemGame, $this->wikiDateHandler));
    }

    public function testCountRealDatesTwo()
    {
        $feedItemGame = new FeedItemGame();
        $feedItemGame->upcoming_date_eu = '2018-04-05';
        $feedItemGame->upcoming_date_us = '2018-03-29';
        $feedItemGame->upcoming_date_jp = 'Unreleased';

        $this->assertEquals(2, $this->wikiSkipper->countRealDates($feedItemGame, $this->wikiDateHandler));
    }

    public function testCountRealDatesNoneXX()
    {
        $feedItemGame = new FeedItemGame();
        $feedItemGame->upcoming_date_eu = '2019-XX';
        $feedItemGame->upcoming_date_us = '2019-XX';
        $feedItemGame->upcoming_date_jp = 'Unreleased';

        $this->assertEquals(0, $this->wikiSkipper->countRealDates($feedItemGame, $this->wikiDateHandler));
    }
}
