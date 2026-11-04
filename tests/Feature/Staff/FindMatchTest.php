<?php

namespace Tests\Feature\Staff;

use App\Domain\AffiliateCodes\CreatorsApi;
use App\Domain\AffiliateCodes\CreatorsApiException;
use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Find match on the Top rated gaps list: one Amazon US search through the Creators API, shown
 * for staff to pick from. Every Amazon call here is faked.
 */
class FindMatchTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.amazon_creators' => [
            'credential_id' => 'test-id',
            'credential_secret' => 'test-secret',
            'credential_version' => '3.1',
        ]]);
        Cache::forget(CreatorsApi::TOKEN_CACHE_KEY);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Find Match Test',
            'email' => 'xx.find.match.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(int $consoleId = Console::ID_SWITCH_1): Game
    {
        $game = new Game();
        $game->title = 'Zqx Match '.uniqid();
        $game->link_title = 'test-match-'.uniqid();
        $game->console_id = $consoleId;
        $game->price_eshop = '9.99';
        $game->save();

        return $game->fresh();
    }

    private function item(string $asin, string $title, array $listings = []): array
    {
        return [
            'asin' => $asin,
            'detailPageURL' => 'https://www.amazon.com/dp/'.$asin.'?tag=switchscores-20&linkCode=osi',
            'images' => ['primary' => ['small' => ['url' => 'https://m.media-amazon.com/images/I/'.$asin.'.jpg']]],
            'itemInfo' => [
                'byLineInfo' => ['brand' => ['displayValue' => 'SEGA']],
                'title' => ['displayValue' => $title],
            ],
            'offersV2' => ['listings' => $listings],
        ];
    }

    private function fakeAmazon(array $items, int $searchStatus = 200): void
    {
        Http::fake([
            CreatorsApi::TOKEN_URL => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            CreatorsApi::SEARCH_URL => $searchStatus === 200
                ? Http::response(['searchResult' => ['items' => $items]])
                : Http::response(['errors' => [['code' => 'AssociateNotEligible']]], $searchStatus),
        ]);
    }

    public function testSearchReadsTitleBrandImageAndBuyBoxPrice()
    {
        $this->fakeAmazon([
            $this->item('B0TEST0001', 'Zqx Game - Nintendo Switch', [
                ['isBuyBoxWinner' => false, 'price' => ['money' => ['displayAmount' => '$18.04']]],
                ['isBuyBoxWinner' => true, 'price' => ['money' => ['displayAmount' => '$18.98']]],
            ]),
            $this->item('B0TEST0002', 'Zqx Game (no offers)'),
        ]);

        $results = app(CreatorsApi::class)->searchItems('Zqx Game Nintendo Switch');

        $this->assertSame([
            'asin' => 'B0TEST0001',
            'title' => 'Zqx Game - Nintendo Switch',
            'brand' => 'SEGA',
            'imageUrl' => 'https://m.media-amazon.com/images/I/B0TEST0001.jpg',
            'price' => '$18.98',
        ], $results[0]);
        $this->assertNull($results[1]['price']);

        Http::assertSent(function (Request $request) {
            return $request->url() === CreatorsApi::SEARCH_URL
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request->hasHeader('x-marketplace', 'www.amazon.com')
                && $request['searchIndex'] === 'VideoGames'
                && $request['partnerTag'] === 'switchscores-20';
        });
    }

    public function testTheTokenIsCachedBetweenSearches()
    {
        $this->fakeAmazon([]);
        $api = app(CreatorsApi::class);

        $api->searchItems('one');
        $api->searchItems('two');

        Http::assertSentCount(3);
        $tokenRequests = Http::recorded(fn (Request $request) => $request->url() === CreatorsApi::TOKEN_URL);
        $this->assertCount(1, $tokenRequests);
    }

    public function testAnApiFailureGivesAMessageWithoutTheCredentials()
    {
        $this->fakeAmazon([], 403);

        try {
            app(CreatorsApi::class)->searchItems('Zqx');
            $this->fail('Expected a CreatorsApiException');
        } catch (CreatorsApiException $e) {
            $this->assertStringContainsString('HTTP 403', $e->getMessage());
            $this->assertStringContainsString('AssociateNotEligible', $e->getMessage());
            $this->assertStringNotContainsString('test-secret', $e->getMessage());
            $this->assertStringNotContainsString('test-token', $e->getMessage());
        }
    }

    public function testMissingCredentialsGiveAMessageAndNoRequest()
    {
        config(['services.amazon_creators.credential_secret' => null]);
        Http::fake();

        $this->expectException(CreatorsApiException::class);
        try {
            app(CreatorsApi::class)->searchItems('Zqx');
        } finally {
            Http::assertNothingSent();
        }
    }

    public function testFindMatchSearchesWithTheConsoleAndFlagsTheOtherConsole()
    {
        $game = $this->makeGame(Console::ID_SWITCH_1);
        $this->fakeAmazon([
            $this->item('B0TEST0001', $game->title.' - Nintendo Switch'),
            $this->item('B0TEST0002', $game->title.' 2S - Nintendo Switch 2'),
        ]);

        $response = $this->actingAs($this->owner())
            ->getJson(route('staff.games.affiliates.findMatch', ['gameId' => $game->id]))
            ->assertStatus(200)
            ->assertJsonPath('keywords', $game->title.' Nintendo Switch')
            ->assertJsonPath('results.0.consoleWarning', null)
            ->assertJsonPath('results.0.viewUrl', 'https://www.amazon.com/dp/B0TEST0001')
            ->assertJsonPath('results.1.consoleWarning', 'Switch 2');

        $this->assertStringNotContainsString('tag=', $response->json('results.0.viewUrl'));
    }

    public function testFindMatchForASwitch2GameFlagsASwitch1Listing()
    {
        $game = $this->makeGame(Console::ID_SWITCH_2);
        $this->fakeAmazon([
            $this->item('B0TEST0003', $game->title.' - Nintendo Switch'),
        ]);

        $this->actingAs($this->owner())
            ->getJson(route('staff.games.affiliates.findMatch', ['gameId' => $game->id]))
            ->assertJsonPath('keywords', $game->title.' Nintendo Switch 2')
            ->assertJsonPath('results.0.consoleWarning', 'Switch 1');
    }

    public function testFindMatchSavesNothing()
    {
        $game = $this->makeGame();
        $this->fakeAmazon([$this->item('B0TEST0001', $game->title)]);

        $this->actingAs($this->owner())
            ->getJson(route('staff.games.affiliates.findMatch', ['gameId' => $game->id]))
            ->assertStatus(200);

        $this->assertNull($game->fresh()->amazon_us_asin);
    }

    public function testFindMatchShowsAnApiFailure()
    {
        $game = $this->makeGame();
        $this->fakeAmazon([], 403);

        $this->actingAs($this->owner())
            ->getJson(route('staff.games.affiliates.findMatch', ['gameId' => $game->id]))
            ->assertStatus(502)
            ->assertJsonPath('error', fn ($error) => str_contains($error, 'HTTP 403'));
    }

    public function testOnlyUsGapsPagesHaveTheButton()
    {
        $game = $this->makeGame(Console::ID_SWITCH_2);
        $game->forceFill(['amazon_us_status' => 'unchecked', 'amazon_uk_status' => 'unchecked'])->save();
        DB::table('game_rank_alltime')->insert([
            'console_id' => $game->console_id, 'game_id' => $game->id, 'game_rank' => 1,
        ]);

        $owner = $this->owner();
        $console = Console::find(Console::ID_SWITCH_2);
        $params = ['console' => $console, 'scope' => 'all-time'];

        $this->actingAs($owner)->get(route('staff.games.affiliates.topRatedGaps', ['region' => 'us'] + $params))
            ->assertStatus(200)
            ->assertSee($game->title)
            ->assertSee('Find match')
            ->assertSee(route('staff.games.affiliates.findMatch', ['gameId' => $game->id]), false);

        $this->actingAs($owner)->get(route('staff.games.affiliates.topRatedGaps', ['region' => 'uk'] + $params))
            ->assertStatus(200)
            ->assertSee($game->title)
            ->assertDontSee('Find match');
    }
}
