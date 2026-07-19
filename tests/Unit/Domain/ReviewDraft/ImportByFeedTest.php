<?php

namespace Tests\Unit\Domain\ReviewDraft;

use App\Domain\ReviewDraft\ImportByFeed;
use App\Domain\Game\Repository as RepoGame;

use Tests\TestCase;

/**
 * Covers title normalisation on import.
 *
 * These characters are invisible in every tool you would normally look at a title with, and
 * they silently stop an anchored match rule from matching at all - so the behaviour is worth
 * pinning down explicitly.
 */
class ImportByFeedTest extends TestCase
{
    /**
     * @var ImportByFeed
     */
    private $importByFeed;

    public function setUp(): void
    {
        parent::setUp();

        $this->importByFeed = new ImportByFeed(resolve(RepoGame::class));
    }

    public function tearDown(): void
    {
        unset($this->importByFeed);

        parent::tearDown();
    }

    public function testCdataMarkersAreRemoved()
    {
        $this->assertEquals(
            'Some Game Review',
            $this->importByFeed->cleanUpTitle('<![CDATA[Some Game Review]]>')
        );
    }

    public function testNewlinesAreRemoved()
    {
        $this->assertEquals(
            'Some Game Review',
            $this->importByFeed->cleanUpTitle("Some Game\r\n Review")
        );
    }

    public function testTrailingNonBreakingSpaceIsRemoved()
    {
        $title = "Pragmata Nintendo Switch 2 Review\xc2\xa0";

        $this->assertEquals('Pragmata Nintendo Switch 2 Review', $this->importByFeed->cleanUpTitle($title));
    }

    public function testInternalNonBreakingSpaceBecomesANormalSpace()
    {
        $title = "Bubsy 4D\xc2\xa0Review";

        $cleaned = $this->importByFeed->cleanUpTitle($title);

        $this->assertEquals('Bubsy 4D Review', $cleaned);
        $this->assertStringNotContainsString("\xc2\xa0", $cleaned);
    }

    public function testZeroWidthSpaceAndByteOrderMarkAreRemoved()
    {
        $title = "\xef\xbb\xbfSome\xe2\x80\x8b Game Review";

        $this->assertEquals('Some Game Review', $this->importByFeed->cleanUpTitle($title));
    }

    public function testSurroundingWhitespaceIsTrimmed()
    {
        $this->assertEquals('Some Game Review', $this->importByFeed->cleanUpTitle('  Some Game Review  '));
    }

    public function testACleanTitleIsLeftAlone()
    {
        $title = 'Rune Factory: Guardians of Azuma Nintendo Switch 2 Edition Review';

        $this->assertEquals($title, $this->importByFeed->cleanUpTitle($title));
    }

    /**
     * The reason all of the above matters: match rules run without the /u modifier, so a
     * multibyte non-breaking space is not whitespace as far as the pattern is concerned.
     */
    public function testNormalisedTitleMatchesAnAnchoredRuleThatTheRawTitleDoesNot()
    {
        $raw = "Pragmata Nintendo Switch 2 Review\xc2\xa0";
        $pattern = '/^(.*?)(?: (?:Nintendo )?Switch(?: 2)?)?\s*Review$/';

        $this->assertSame(0, preg_match($pattern, $raw));
        $this->assertSame(1, preg_match($pattern, $this->importByFeed->cleanUpTitle($raw)));
    }
}
