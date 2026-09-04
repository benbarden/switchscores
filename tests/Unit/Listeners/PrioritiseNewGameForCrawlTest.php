<?php

namespace Tests\Unit\Listeners;

use App\Events\GameCreated;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * A newly added game should go to the front of the crawl queue whatever route added it.
 *
 * Before #147 this was set inline by the JSON import and the weekly batch importer only,
 * so games added by hand, from a data source parsed item, or via the Release Hub sat in
 * the ordinary queue behind ~15k others - the opposite of what you want for the game
 * someone is working on right now.
 */
class PrioritiseNewGameForCrawlTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'      => 'Crawl Priority Test Game',
            'link_title' => 'crawl-priority-test-game',
            'console_id' => Console::ID_SWITCH_1,
        ], $overrides));
    }

    public function testGameCreatedEventSetsCrawlPriority()
    {
        $game = $this->makeGame();

        $this->assertFalse((bool) $game->crawl_priority);

        event(new GameCreated($game));

        $this->assertTrue((bool) $game->fresh()->crawl_priority);
    }

    public function testListenerIsRegisteredForTheEvent()
    {
        $listeners = app('events')->getListeners(GameCreated::class);

        $this->assertNotEmpty(
            $listeners,
            'GameCreated has no listeners - crawl priority will silently stop being set.'
        );
    }

    public function testAlreadyPrioritisedGameIsLeftAlone()
    {
        $game = $this->makeGame(['crawl_priority' => true]);
        $updatedAt = $game->updated_at;

        event(new GameCreated($game));

        $this->assertTrue((bool) $game->fresh()->crawl_priority);
        $this->assertEquals($updatedAt, $game->fresh()->updated_at);
    }
}
