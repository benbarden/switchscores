<?php

namespace Tests\Feature\PublicSite;

use App\Enums\GameStatus;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The buyer's guide: a region picker at /buyers-guide, and a page per region whose Amazon links
 * carry the guide's own tracking IDs.
 */
class BuyersGuideTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGame(string $title, int $consoleId, ?string $usAsin, ?string $ukAsin): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-buyers-guide-'.uniqid();
        $game->console_id = $consoleId;
        $game->game_status = GameStatus::ACTIVE;
        $game->amazon_us_asin = $usAsin;
        $game->amazon_uk_asin = $ukAsin;
        $game->save();
        return $game;
    }

    private function useContent(array $hardware, array $switch2Ids, array $switch1Ids = []): void
    {
        config([
            'buyers-guide.year' => '2031',
            'buyers-guide.updated' => '2031-11-20',
            'buyers-guide.updated_note' => 'for the test season',
            'buyers-guide.hardware' => $hardware,
            'buyers-guide.games.switch-2' => $switch2Ids,
            'buyers-guide.games.switch-1' => $switch1Ids,
        ]);
    }

    public function testLandingLinksToBothRegions()
    {
        $this->get(route('buyersGuide.landing'))
            ->assertStatus(200)
            ->assertSee(route('buyersGuide.region', ['region' => 'us']), false)
            ->assertSee(route('buyersGuide.region', ['region' => 'uk']), false);
    }

    public function testUnknownRegionIsNotFound()
    {
        $this->get('/buyers-guide/fr')->assertStatus(404);
    }

    public function testUsPageUsesTheGuideTrackingId()
    {
        $game = $this->makeGame('Buyers Guide Test Game', Console::ID_SWITCH_2, 'BGTESTUS01', 'BGTESTUK01');
        $this->useContent(
            [['title' => 'Test Console', 'note' => null, 'asin_us' => 'BGHWUS0001', 'asin_uk' => null]],
            [$game->id]
        );

        $this->get(route('buyersGuide.region', ['region' => 'us']))
            ->assertStatus(200)
            ->assertSee('Nintendo Switch buyer&#039;s guide 2031 (US)', false)
            ->assertSee('Updated 20 November 2031 for the test season.')
            ->assertSee('https://www.amazon.com/dp/BGHWUS0001/?tag=ssbuyersguide-20', false)
            ->assertSee('https://www.amazon.com/dp/BGTESTUS01/?tag=ssbuyersguide-20', false)
            ->assertDontSee('amazon.co.uk', false)
            ->assertSee(route('buyersGuide.region', ['region' => 'uk']), false);
    }

    public function testUkPageUsesUkLinksAndLeavesOutItemsWithoutAUkCode()
    {
        $withUk = $this->makeGame('Buyers Guide UK Game', Console::ID_SWITCH_2, 'BGTESTUS02', 'BGTESTUK02');
        $usOnly = $this->makeGame('Buyers Guide US Only Game', Console::ID_SWITCH_2, 'BGTESTUS03', null);
        $this->useContent(
            [
                ['title' => 'UK Console', 'note' => null, 'asin_us' => 'BGHWUS0002', 'asin_uk' => 'BGHWUK0002'],
                ['title' => 'US Only Accessory', 'note' => null, 'asin_us' => 'BGHWUS0003', 'asin_uk' => null],
            ],
            [$withUk->id, $usOnly->id]
        );

        $this->get(route('buyersGuide.region', ['region' => 'uk']))
            ->assertStatus(200)
            ->assertSee('https://www.amazon.co.uk/dp/BGHWUK0002/?tag=ssbuyersguide-21', false)
            ->assertSee('https://www.amazon.co.uk/dp/BGTESTUK02/?tag=ssbuyersguide-21', false)
            ->assertDontSee('US Only Accessory')
            // The game still shows, without a buy button
            ->assertSee('Buyers Guide US Only Game')
            ->assertDontSee('amazon.com/', false);
    }

    public function testGamesKeepTheOrderTheyAreListedIn()
    {
        $first = $this->makeGame('Buyers Guide Order Zebra', Console::ID_SWITCH_1, 'BGTESTUS04', null);
        $second = $this->makeGame('Buyers Guide Order Aardvark', Console::ID_SWITCH_1, 'BGTESTUS05', null);
        $this->useContent([], [], [$first->id, $second->id]);

        $this->get(route('buyersGuide.region', ['region' => 'us']))
            ->assertStatus(200)
            ->assertSeeInOrder(['Buyers Guide Order Zebra', 'Buyers Guide Order Aardvark']);
    }
}
