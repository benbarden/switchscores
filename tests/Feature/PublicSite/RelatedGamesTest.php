<?php

namespace Tests\Feature\PublicSite;

use App\Domain\GameLists\RelatedGames;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Models\Category;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameCollection;
use App\Models\GameSeries;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Related games on the public game page: one list from the series, collection and category,
 * most specific first, no duplicates, at most 8. The repository is swapped for a stand-in, so
 * the random sampling in its relatedBy* methods doesn't affect the results.
 */
class RelatedGamesTest extends TestCase
{
    use DatabaseTransactions;

    private int $nextId = 9000000;

    private function games(int $count): array
    {
        $games = [];
        for ($i = 0; $i < $count; $i++) {
            $game = new Game(['title' => 'Zqx Related '.$this->nextId]);
            $game->id = $this->nextId++;
            $game->link_title = 'zqx-related-'.$game->id;
            $game->console_id = Console::ID_SWITCH_1;
            $games[] = $game;
        }
        return $games;
    }

    private function subject(?array $series, ?array $collection, ?array $category): Game
    {
        $game = new Game(['title' => 'Zqx Related Subject']);
        $game->id = 8999999;
        $game->console_id = Console::ID_SWITCH_1;

        if ($series !== null) {
            $game->series_id = 1;
            $game->setRelation('series', new GameSeries(['series' => 'Zqx Series', 'link_title' => 'zqx-series']));
        }
        if ($collection !== null) {
            $game->collection_id = 1;
            $game->setRelation('gameCollection', new GameCollection(['name' => 'Zqx Collection', 'link_title' => 'zqx-collection']));
        }
        if ($category !== null) {
            $game->category_id = 1;
            $game->setRelation('category', new Category(['name' => 'Zqx Category', 'link_title' => 'zqx-category']));
        }

        // A stand-in rather than a Mockery mock: Mockery can't generate a double for this class.
        $this->app->instance(GameListsRepository::class, new class(app('cache'), $series, $collection, $category) extends GameListsRepository {
            public function __construct($cache, private ?array $series, private ?array $collection, private ?array $category)
            {
                parent::__construct($cache);
            }
            public function relatedBySeries($consoleId, $seriesId, $excludeGameId, $limit = 3) { return collect($this->series ?? []); }
            public function relatedByCollection($consoleId, $collectionId, $excludeGameId, $limit = 3) { return collect($this->collection ?? []); }
            public function relatedByCategory($consoleId, $categoryId, $excludeGameId, $limit = 3) { return collect($this->category ?? []); }
        });

        return $game;
    }

    public function testMostSpecificFirstWithNoDuplicatesAndAtMostEight()
    {
        $series = $this->games(4);
        $collection = array_merge([$series[0]], $this->games(3));
        $category = $this->games(4);
        $game = $this->subject($series, $collection, $category);

        $related = app(RelatedGames::class)->forGame($game);

        $this->assertCount(8, $related['games']);
        $this->assertSame(
            ['Zqx Series', 'Zqx Series', 'Zqx Series', 'Zqx Series', 'Zqx Collection', 'Zqx Collection', 'Zqx Collection', 'Zqx Category'],
            array_column($related['games'], 'label')
        );
        $this->assertSame($series[0]->id, $related['games'][0]['game']->id);
        $this->assertTrue($related['mixed']);
        $this->assertSame('More like this', $related['heading']);
        $this->assertSame(['Zqx Series', 'Zqx Collection', 'Zqx Category'], array_column($related['sources'], 'name'));
    }

    public function testOneSourceKeepsTheOldHeading()
    {
        $game = $this->subject(null, null, $this->games(4));

        $related = app(RelatedGames::class)->forGame($game);

        $this->assertCount(4, $related['games']);
        $this->assertFalse($related['mixed']);
        $this->assertSame('More games in Zqx Category', $related['heading']);
    }

    public function testAnEmptySourceStillGetsItsViewAllLink()
    {
        $game = $this->subject([], null, $this->games(2));

        $related = app(RelatedGames::class)->forGame($game);

        $this->assertFalse($related['mixed']);
        $this->assertSame('More games in Zqx Category', $related['heading']);
        $this->assertSame(['Zqx Series', 'Zqx Category'], array_column($related['sources'], 'name'));
    }

    public function testNoSourcesGiveNothing()
    {
        $related = app(RelatedGames::class)->forGame($this->subject(null, null, null));

        $this->assertSame([], $related['games']);
        $this->assertSame([], $related['sources']);
    }
}
