<?php

namespace Tests\Unit\Domain\GameGroup;

use App\Domain\GameGroup\Matcher;
use App\Domain\GameGroup\Repository as GameGroupRepository;
use App\Domain\GameGroup\TitleMatchKey;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameGroup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Finding likely Switch 1 / Switch 2 pairs (#44). Runs over localdev's real games too, so
 * the assertions only look at this test's own games, picked out by id.
 */
class MatcherTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGame(string $title, int $consoleId, ?string $linkTitle = null): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = $linkTitle ?? 'test-matcher-'.uniqid();
        $game->console_id = $consoleId;
        $game->save();
        return $game;
    }

    private function pairFor($pairs, Game $switch2): ?array
    {
        return $pairs->first(fn ($pair) => $pair['switch2']->id == $switch2->id);
    }

    private function matcher(): Matcher
    {
        return new Matcher(new TitleMatchKey());
    }

    public function testLinkTitlePair()
    {
        $linkTitle = 'test-matcher-lt-'.uniqid();
        $switch1 = $this->makeGame('Matcher Test Zq One', Console::ID_SWITCH_1, $linkTitle);
        $switch2 = $this->makeGame('Matcher Test Zq One', Console::ID_SWITCH_2, $linkTitle);

        $pair = $this->pairFor($this->matcher()->linkTitlePairs(), $switch2);

        $this->assertSame($switch1->id, $pair['switch1']->id);
        $this->assertSame(Matcher::SOURCE_LINK_TITLE, $pair['source']);
    }

    public function testTitleKeyPairOnlyInAllPairs()
    {
        $switch1 = $this->makeGame('Matcher Test Zq Two', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Matcher Test Zq Two: Nintendo Switch 2 Edition', Console::ID_SWITCH_2);

        $this->assertNull($this->pairFor($this->matcher()->linkTitlePairs(), $switch2));

        $pair = $this->pairFor($this->matcher()->allPairs(), $switch2);
        $this->assertSame($switch1->id, $pair['switch1']->id);
        $this->assertSame(Matcher::SOURCE_TITLE_KEY, $pair['source']);
    }

    public function testGroupedGamesAreLeftOut()
    {
        $linkTitle = 'test-matcher-grouped-'.uniqid();
        $switch1 = $this->makeGame('Matcher Test Zq Three', Console::ID_SWITCH_1, $linkTitle);
        $switch2 = $this->makeGame('Matcher Test Zq Three', Console::ID_SWITCH_2, $linkTitle);
        $repo = new GameGroupRepository();
        $group = $repo->create('Matcher Test Zq Three', GameGroup::MATCH_IDENTICAL);
        $repo->linkGame($group, $switch1);
        $repo->linkGame($group, $switch2);

        $this->assertNull($this->pairFor($this->matcher()->allPairs(), $switch2));
    }

    public function testSeveralSwitch1MatchesAreAmbiguous()
    {
        $this->makeGame('Matcher Test Zq Four', Console::ID_SWITCH_1);
        $this->makeGame('Matcher Test: Zq Four', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Matcher Test Zq Four (Switch 2)', Console::ID_SWITCH_2);

        $matcher = $this->matcher();
        $pairs = $matcher->allPairs();

        $this->assertNull($this->pairFor($pairs, $switch2));
        $this->assertGreaterThanOrEqual(1, $matcher->ambiguousCount());
    }
}
