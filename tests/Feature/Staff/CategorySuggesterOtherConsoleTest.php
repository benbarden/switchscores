<?php

namespace Tests\Feature\Staff;

use App\Domain\WeeklyBatch\CategorySuggester;
use App\Models\Category;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Category suggestions from the same game on the other console, whichever side has the console
 * suffix in its title.
 */
class CategorySuggesterOtherConsoleTest extends TestCase
{
    use DatabaseTransactions;

    private function category(): Category
    {
        return Category::create(['name' => 'Zqx Category '.uniqid(), 'link_title' => 'zqx-category-'.uniqid()]);
    }

    private function makeGame(string $title, int $consoleId, ?Category $category): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-category-suggester-'.uniqid();
        $game->console_id = $consoleId;
        $game->category_id = $category?->id;
        $game->save();
        return $game;
    }

    private function suggest(string $title, string $console): array
    {
        return app(CategorySuggester::class)->suggestFor($title, $console);
    }

    public function testASwitch1GameMatchesASwitch2EditionTitle()
    {
        $category = $this->category();
        $this->makeGame('Zqx Galactic: Survivor - Nintendo Switch 2 Edition', Console::ID_SWITCH_2, $category);

        $suggestion = $this->suggest('Zqx Galactic: Survivor', 'switch-1');

        $this->assertSame($category->name, $suggestion['category']);
        $this->assertStringContainsString('Other console: Zqx Galactic: Survivor - Nintendo Switch 2 Edition', $suggestion['reason']);
    }

    public function testASwitch2EditionTitleMatchesASwitch1Game()
    {
        $category = $this->category();
        $this->makeGame('Zqx Galactic: Survivor', Console::ID_SWITCH_1, $category);

        $suggestion = $this->suggest('Zqx Galactic: Survivor - Nintendo Switch 2 Edition', 'switch-2');

        $this->assertSame($category->name, $suggestion['category']);
    }

    public function testPunctuationDifferencesMatch()
    {
        $category = $this->category();
        $this->makeGame("Zqx Meier's Rocks & Stones", Console::ID_SWITCH_1, $category);

        $suggestion = $this->suggest('Zqx Meiers Rocks and Stones', 'switch-2');

        $this->assertSame($category->name, $suggestion['category']);
    }

    public function testASimilarTitleDoesNotMatch()
    {
        $this->makeGame('Zqx Quest I & II', Console::ID_SWITCH_2, $this->category());

        $this->assertNull($this->suggest('Zqx Quest III', 'switch-1')['category']);
    }

    public function testAGameOnTheSameConsoleDoesNotMatch()
    {
        $this->makeGame('Zqx Same Console', Console::ID_SWITCH_1, $this->category());

        $this->assertNull($this->suggest('Zqx Same Console', 'switch-1')['category']);
    }

    public function testAnUncategorisedGameIsSkipped()
    {
        $category = $this->category();
        $this->makeGame('Zqx Twice', Console::ID_SWITCH_2, null);
        $this->makeGame('Zqx Twice - Nintendo Switch 2 Edition', Console::ID_SWITCH_2, $category);

        $this->assertSame($category->name, $this->suggest('Zqx Twice', 'switch-1')['category']);
    }
}
