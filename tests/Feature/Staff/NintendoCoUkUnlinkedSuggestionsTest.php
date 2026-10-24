<?php

namespace Tests\Feature\Staff;

use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Unlinked suggestions page (#192).
 */
class NintendoCoUkUnlinkedSuggestionsTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Unlinked Suggestions Test',
            'email' => 'xx.unlinked.suggestions.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(string $title, int $consoleId = Console::ID_SWITCH_2): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-unlinked-suggestions-'.uniqid();
        $game->console_id = $consoleId;
        $game->save();
        return $game;
    }

    private function makeItem(string $title, int $consoleId = Console::ID_SWITCH_2): DataSourceParsed
    {
        return DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => $consoleId,
            'link_id' => random_int(2000000000, 2100000000),
            'title' => $title,
            'release_date_eu' => '2026-10-28',
            'url' => '/en-gb/Games/Nintendo-Switch-2-games/Zqx-Test-1.html',
        ]);
    }

    private function link(Game $game, DataSourceParsed $item)
    {
        return $this->actingAs($this->owner())->post(
            route('staff.data-sources.nintendo-co-uk.unlinked.suggestions.link'),
            ['game_id' => $game->id, 'link_id' => $item->link_id]
        );
    }

    public function testPageShowsMatchesAndNoMatches()
    {
        $game = $this->makeGame('Zqx Suggestions Match');
        $this->makeItem('Zqx Suggestions Match');
        $this->makeItem('Zqx Suggestions Nothing Here');

        $response = $this->actingAs($this->owner())->get(route('staff.data-sources.nintendo-co-uk.unlinked.suggestions'));

        $response->assertStatus(200);
        $response->assertSee('name="game_id" value="'.$game->id.'"', false);
        $response->assertSee('Zqx Suggestions Nothing Here');
        $response->assertSee('q=Zqx%20Suggestions%20Nothing%20Here&amp;f=147394-91-126551', false);
    }

    public function testLinkDoesWhatEditLinkDoes()
    {
        $this->mock(DownloadPackshotHelper::class, function ($mock) {
            $mock->shouldReceive('downloadForGame')->once();
        });
        $game = $this->makeGame('Zqx Suggestions Match');
        $item = $this->makeItem('Zqx Suggestions Match');

        $response = $this->link($game, $item);

        $response->assertRedirect(route('staff.data-sources.nintendo-co-uk.unlinked.suggestions'));
        $response->assertSessionHas('unlinkedSuccess', $game->id);
        $game->refresh();
        $this->assertEquals($item->link_id, $game->eshop_europe_fs_id);
        $this->assertSame(Game::FORMAT_AVAILABLE, $game->format_digital);
        $this->assertEquals($game->id, $item->fresh()->game_id);
    }

    public function testRefusesAnItemThatIsAlreadyLinked()
    {
        $this->mock(DownloadPackshotHelper::class, function ($mock) {
            $mock->shouldReceive('downloadForGame')->never();
        });
        $other = $this->makeGame('Zqx Suggestions Other');
        $game = $this->makeGame('Zqx Suggestions Match');
        $item = $this->makeItem('Zqx Suggestions Match');
        $item->game_id = $other->id;
        $item->save();

        $response = $this->link($game, $item);

        $response->assertSessionHas('unlinkedError');
        $this->assertNull($game->fresh()->eshop_europe_fs_id);
    }

    public function testRefusesADifferentConsole()
    {
        $this->mock(DownloadPackshotHelper::class, function ($mock) {
            $mock->shouldReceive('downloadForGame')->never();
        });
        $game = $this->makeGame('Zqx Suggestions Match', Console::ID_SWITCH_1);
        $item = $this->makeItem('Zqx Suggestions Match');

        $response = $this->link($game, $item);

        $response->assertSessionHas('unlinkedError');
        $this->assertNull($item->fresh()->game_id);
    }
}
