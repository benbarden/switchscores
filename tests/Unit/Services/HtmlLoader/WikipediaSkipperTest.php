<?php

namespace Tests\Unit\Services\HtmlLoader;

use App\Services\HtmlLoader\Wikipedia\Skipper as WikiSkipper;
use App\Services\HtmlLoader\Wikipedia\DateHandler as WikiDateHandler;
use Illuminate\Support\Collection;
use Tests\TestCase;

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

    public function setUp(): void
    {
        $this->wikiSkipper = new WikiSkipper();
        $this->wikiDateHandler = new WikiDateHandler();

        parent::setUp();
    }

    public function tearDown(): void
    {
        unset($this->wikiSkipper);
        unset($this->wikiDateHandler);

        parent::tearDown();
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
}
