<?php

namespace Tests\Unit\Services\Feed;

use App\Services\Feed\TitleParser;
use App\FeedItemReview;
use App\ReviewSite;

use Illuminate\Support\Collection;
use Tests\TestCase;
#use Illuminate\Foundation\Testing\DatabaseMigrations;
#use Illuminate\Foundation\Testing\DatabaseTransactions;

class TitleParserTest extends TestCase
{
    /**
     * @var TitleParser
     */
    private $titleParser;

    public function setUp()
    {
        $this->titleParser = new TitleParser();

        parent::setUp();
    }

    public function tearDown()
    {
        unset($this->titleParser);

        parent::tearDown();
    }

    public function testTitleStorage()
    {
        $title = 'This is a Switch review';

        $this->titleParser->setTitle($title);

        $this->assertEquals($title, $this->titleParser->getTitle());
    }

    public function testCleanupText()
    {
        $this->titleParser->setTitle('Syberia ');
        $this->titleParser->cleanupText();
        $this->assertEquals('Syberia', $this->titleParser->getTitle());
    }

    public function testCleanupTextDoubleSpace()
    {
        $this->titleParser->setTitle('Syberia  Review');
        $this->titleParser->cleanupText();
        $this->assertEquals('Syberia Review', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripReviewText()
    {
        $this->titleParser->setTitle('Syberia Review');
        $this->titleParser->stripReviewText();
        $this->titleParser->cleanupText();
        $this->assertEquals('Syberia', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripReviewTextWithColon()
    {
        $this->titleParser->setTitle('Review: Teslagrad (Nintendo Switch)');
        $this->titleParser->stripReviewText();
        $this->titleParser->cleanupText();
        $this->assertEquals('Teslagrad (Nintendo Switch)', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripMiniReviewTextWithColon()
    {
        $this->titleParser->setTitle('Mini-Review: Astro Bears Party (Nintendo Switch)');
        $this->titleParser->stripReviewText();
        $this->titleParser->cleanupText();
        $this->assertEquals('Astro Bears Party (Nintendo Switch)', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripReviewTextWithBrackets()
    {
        $this->titleParser->setTitle('[Review] Embers of Mirrim (Nintendo Switch)');
        $this->titleParser->stripReviewText();
        $this->titleParser->cleanupText();
        $this->assertEquals('Embers of Mirrim (Nintendo Switch)', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripPlatformTextEshop()
    {
        $this->titleParser->setTitle('One More Dungeon (Switch eShop) Review');
        $this->titleParser->stripPlatformText();
        $this->titleParser->cleanupText();
        $this->assertEquals('One More Dungeon Review', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripPlatformTextNintendoSwitch()
    {
        $this->titleParser->setTitle('Party Planet (Nintendo Switch) Review');
        $this->titleParser->stripPlatformText();
        $this->titleParser->cleanupText();
        $this->assertEquals('Party Planet Review', $this->titleParser->getTitle());
    }

    /**
     * @depends testCleanupText
     */
    public function testStripPlatformTextSwitch()
    {
        $this->titleParser->setTitle('Review: Snipperclips (Switch)');
        $this->titleParser->stripPlatformText();
        $this->titleParser->cleanupText();
        $this->assertEquals('Review: Snipperclips', $this->titleParser->getTitle());
    }
}
