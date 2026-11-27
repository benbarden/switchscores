<?php

namespace Tests\Feature\PublicSite;

use App\Enums\GameStatus;
use App\Models\Category;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * The browse by category landing page. Nearly every game has a category, so the page must count
 * games in the database: loading them to count them ran it out of memory on prod.
 */
class BrowseCategoryLandingTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCategory(string $name, ?int $parentId = null): Category
    {
        return Category::create([
            'name' => $name,
            'link_title' => 'test-browse-landing-'.uniqid(),
            'parent_id' => $parentId,
        ]);
    }

    private function makeGames(Category $category, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $game = new Game();
            $game->title = 'Browse Landing Test Game '.uniqid();
            $game->link_title = 'test-browse-landing-'.uniqid();
            $game->console_id = Console::ID_SWITCH_1;
            $game->game_status = GameStatus::ACTIVE;
            $game->category_id = $category->id;
            $game->save();
        }
    }

    public function testShowsGameCountsWithoutLoadingGames()
    {
        $parent = $this->makeCategory('Browse Landing Test Parent');
        $child = $this->makeCategory('Browse Landing Test Child', $parent->id);
        $this->makeGames($parent, 3);
        $this->makeGames($child, 2);

        $gamesLoaded = 0;
        Event::listen('eloquent.retrieved: '.Game::class, function () use (&$gamesLoaded) {
            $gamesLoaded++;
        });

        $response = $this->get(route('browse.byCategory.landing'));

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Browse Landing Test Parent', '3', 'Browse Landing Test Child', '2']);
        $this->assertSame(0, $gamesLoaded);
    }
}
