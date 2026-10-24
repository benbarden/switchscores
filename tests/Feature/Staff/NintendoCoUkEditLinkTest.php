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
 * The Edit link page (staff.games.editNintendoCoUk). Pins down what saving the form does, since
 * Unlinked suggestions (#192) links games through the same code.
 */
class NintendoCoUkEditLinkTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Edit Link Test',
            'email' => 'xx.edit.link.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(string $title): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-edit-link-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();
        return $game;
    }

    private function makeItem(string $title, ?int $gameId = null): DataSourceParsed
    {
        return DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => Console::ID_SWITCH_2,
            'game_id' => $gameId,
            // link_id is an int column; real ones are around 1-3 million
            'link_id' => random_int(2000000000, 2100000000),
            'title' => $title,
            'release_date_eu' => '2026-10-28',
        ]);
    }

    private function save(Game $game, ?DataSourceParsed $item)
    {
        return $this->actingAs($this->owner())->post(
            route('staff.games.editNintendoCoUk', ['gameId' => $game->id]),
            ['nintendo_co_uk_link_id' => $item?->link_id]
        );
    }

    private function expectPackshotDownloads(int $times): void
    {
        // The real helper crawls the Nintendo store page
        $this->mock(DownloadPackshotHelper::class, function ($mock) use ($times) {
            $mock->shouldReceive('downloadForGame')->times($times);
        });
    }

    public function testLinkingAnUnlinkedGame()
    {
        $this->expectPackshotDownloads(1);
        $game = $this->makeGame('Edit Link Test Game');
        $item = $this->makeItem('Edit Link Test Game');

        $response = $this->save($game, $item);

        $response->assertRedirect('/staff/games/detail/'.$game->id.'?lastaction=edit&lastgameid='.$game->id);
        $game->refresh();
        $this->assertEquals($item->link_id, $game->eshop_europe_fs_id);
        $this->assertSame(Game::FORMAT_AVAILABLE, $game->format_digital);
        $this->assertEquals('2026-10-28', $game->eu_release_date);
        $this->assertEquals($game->id, $item->fresh()->game_id);
    }

    public function testMovingTheLinkToAnotherItem()
    {
        $this->expectPackshotDownloads(1);
        $game = $this->makeGame('Edit Link Test Game');
        $oldItem = $this->makeItem('Edit Link Test Game', $game->id);
        $game->eshop_europe_fs_id = $oldItem->link_id;
        $game->save();
        $newItem = $this->makeItem('Edit Link Test Game Deluxe');

        $this->save($game, $newItem);

        $this->assertNull($oldItem->fresh()->game_id);
        $this->assertEquals($game->id, $newItem->fresh()->game_id);
        $this->assertEquals($newItem->link_id, $game->fresh()->eshop_europe_fs_id);
    }

    public function testClearingTheLink()
    {
        $this->expectPackshotDownloads(0);
        $game = $this->makeGame('Edit Link Test Game');
        $item = $this->makeItem('Edit Link Test Game', $game->id);
        $game->eshop_europe_fs_id = $item->link_id;
        $game->format_digital = Game::FORMAT_AVAILABLE;
        $game->save();

        $this->save($game, null);

        $game->refresh();
        $this->assertNull($game->eshop_europe_fs_id);
        // Clearing the link keeps the digital status
        $this->assertSame(Game::FORMAT_AVAILABLE, $game->format_digital);
        $this->assertNull($item->fresh()->game_id);
    }
}
