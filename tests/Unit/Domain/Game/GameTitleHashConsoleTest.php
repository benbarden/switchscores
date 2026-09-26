<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GameTitleHash\HashGenerator;

use App\Models\Console;
use App\Models\GameTitleHash;

use Tests\TestCase;

/**
 * Covers byTitleGroup() when a title exists on both consoles - the case where it used to
 * return whichever row the database gave back first, and so published a console-specific
 * feed's review on the wrong console's game.
 *
 * Writes to the development database. The rows carry a marker title and made-up game ids
 * (game_title_hashes has no foreign key), and are removed in setUp as well as tearDown so a
 * test that dies midway doesn't leave the next run asserting against stale rows.
 */
class GameTitleHashConsoleTest extends TestCase
{
    const MARKER_TITLE = 'Console Match Test Title Zqxv';
    const OTHER_MARKER_TITLE = 'Console Match Test Other Title Zqxv';

    const GAME_ID_SWITCH_1 = 999999001;
    const GAME_ID_SWITCH_2 = 999999002;
    const GAME_ID_OTHER = 999999003;

    /**
     * @var GameTitleHashRepository
     */
    private $repoGameTitleHash;

    public function setUp(): void
    {
        parent::setUp();

        $this->repoGameTitleHash = new GameTitleHashRepository();

        $this->cleanUpTestRecords();

        $this->createHash(self::MARKER_TITLE, self::GAME_ID_SWITCH_1, Console::ID_SWITCH_1);
        $this->createHash(self::MARKER_TITLE, self::GAME_ID_SWITCH_2, Console::ID_SWITCH_2);
        $this->createHash(self::OTHER_MARKER_TITLE, self::GAME_ID_OTHER, Console::ID_SWITCH_1);
    }

    public function tearDown(): void
    {
        $this->cleanUpTestRecords();

        unset($this->repoGameTitleHash);

        parent::tearDown();
    }

    private function cleanUpTestRecords()
    {
        GameTitleHash::whereIn('title', [self::MARKER_TITLE, self::OTHER_MARKER_TITLE])->delete();
    }

    private function createHash($title, $gameId, $consoleId)
    {
        GameTitleHash::create([
            'title' => $title,
            'title_hash' => (new HashGenerator())->generateHash($title),
            'game_id' => $gameId,
            'console_id' => $consoleId,
        ]);
    }

    public function testATitleOnBothConsolesIsNotMatchedWithoutAConsole()
    {
        $this->assertNull($this->repoGameTitleHash->byTitleGroup([self::MARKER_TITLE]));
    }

    public function testATitleOnBothConsolesMatchesTheGameOnTheGivenConsole()
    {
        $switch1 = $this->repoGameTitleHash->byTitleGroup([self::MARKER_TITLE], Console::ID_SWITCH_1);
        $switch2 = $this->repoGameTitleHash->byTitleGroup([self::MARKER_TITLE], Console::ID_SWITCH_2);

        $this->assertEquals(self::GAME_ID_SWITCH_1, $switch1->game_id);
        $this->assertEquals(self::GAME_ID_SWITCH_2, $switch2->game_id);
    }

    public function testATitleOnOneConsoleStillMatchesWithoutAConsole()
    {
        $match = $this->repoGameTitleHash->byTitleGroup([self::OTHER_MARKER_TITLE]);

        $this->assertEquals(self::GAME_ID_OTHER, $match->game_id);
    }

    public function testATitleIsNotMatchedOnTheWrongConsole()
    {
        $this->assertNull(
            $this->repoGameTitleHash->byTitleGroup([self::OTHER_MARKER_TITLE], Console::ID_SWITCH_2)
        );
    }

    /**
     * Title variants that all point to one game are still a single match, not ambiguity.
     */
    public function testVariantsForTheSameGameAreASingleMatch()
    {
        $this->createHash(self::OTHER_MARKER_TITLE.' Deluxe', self::GAME_ID_OTHER, Console::ID_SWITCH_1);

        $match = $this->repoGameTitleHash->byTitleGroup([
            self::OTHER_MARKER_TITLE,
            self::OTHER_MARKER_TITLE.' Deluxe',
        ]);

        GameTitleHash::where('title', self::OTHER_MARKER_TITLE.' Deluxe')->delete();

        $this->assertEquals(self::GAME_ID_OTHER, $match->game_id);
    }
}
