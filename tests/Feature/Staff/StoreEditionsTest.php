<?php

namespace Tests\Feature\Staff;

use App\Domain\DataSource\NintendoCoUk\StoreEditions;
use App\Models\Console;
use App\Models\DataSource;
use App\Models\DataSourceParsed;
use App\Models\DataSourcePrice;
use App\Models\DataSourceRaw;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Store editions from the price API (#161): a game's SKUs with their type and price, on the
 * staff game detail tab and the Store editions list.
 */
class StoreEditionsTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Store Editions Test',
            'email' => 'xx.store.editions.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    /**
     * A linked game whose raw record has these SKUs; a null price means not fetched.
     */
    private function makeGame(array $skus, int $consoleId = Console::ID_SWITCH_2): Game
    {
        $game = new Game();
        $game->title = 'Zqx Store Editions '.uniqid();
        $game->link_title = 'test-store-editions-'.uniqid();
        $game->console_id = $consoleId;
        $game->price_eshop = '49.99';
        $game->save();

        $linkId = random_int(2000000000, 2100000000);
        DataSourceRaw::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => $consoleId,
            'link_id' => $linkId,
            'title' => $game->title,
            'source_data_json' => json_encode(['nsuid_txt' => array_keys($skus)]),
            'is_delisted' => 0,
        ]);
        DataSourceParsed::create([
            'source_id' => DataSource::DSID_NINTENDO_CO_UK,
            'console_id' => $consoleId,
            'game_id' => $game->id,
            'link_id' => $linkId,
            'title' => $game->title,
        ]);
        foreach ($skus as $nsuid => $price) {
            if ($price === null) continue;
            DataSourcePrice::create([
                'nsuid' => $nsuid,
                'console_id' => $consoleId,
                'sales_status' => 'onsale',
                'regular_price' => $price,
                'fetched_at' => now(),
            ]);
        }
        return $game;
    }

    public function testForGameListsEachSkuStandardFirst()
    {
        $game = $this->makeGame([
            '70070000970002' => '69.99',
            '70010000970001' => '49.99',
            '70050000970003' => null,
        ]);

        $rows = app(StoreEditions::class)->forGame($game);

        $this->assertSame(['Standard', 'Deluxe edition or bundle', 'Upgrade pack'], array_column($rows, 'label'));
        $this->assertSame('49.99', $rows[0]['price']->regular_price);
        $this->assertNull($rows[2]['price']);
    }

    public function testAnUnlinkedGameHasNone()
    {
        $game = new Game();
        $game->title = 'Zqx Store Editions Unlinked';
        $game->link_title = 'test-store-editions-unlinked-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();

        $this->assertSame([], app(StoreEditions::class)->forGame($game));
    }

    public function testTheListHasOnlyGamesWithMoreThanTheStandardEdition()
    {
        $withDeluxe = $this->makeGame(['70010000970011' => '49.99', '70070000970012' => '69.99']);
        $standardOnly = $this->makeGame(['70010000970013' => '19.99']);

        $ids = array_map(fn ($row) => $row['game']->id, app(StoreEditions::class)->gamesWithEditions(Console::ID_SWITCH_2));

        $this->assertContains($withDeluxe->id, $ids);
        $this->assertNotContains($standardOnly->id, $ids);
    }

    public function testPagesShowTheEditions()
    {
        $game = $this->makeGame(['70010000970021' => '49.99', '70070000970022' => '69.99']);

        $this->actingAs($this->owner())->get(route('staff.games.detail', ['gameId' => $game->id]))
            ->assertStatus(200)
            ->assertSee('Store editions (2)')
            ->assertSee('£69.99');

        $this->actingAs($this->owner())->get(route('staff.data-sources.nintendo-co-uk.store-editions', ['console' => 2]))
            ->assertStatus(200)
            ->assertSee($game->title);
    }
}
