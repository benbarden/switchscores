<?php

namespace Tests\Feature\PublicSite;

use App\Domain\AffiliateCodes\CreatorsApi;
use App\Domain\BuyersGuide\OfferRefresher;
use App\Domain\BuyersGuide\OfferStore;
use App\Enums\GameStatus;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The buyer's guide Deals section and hardware images, from the hourly Amazon US offers refresh.
 */
class BuyersGuideDealsTest extends TestCase
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
        app(OfferStore::class)->forget();

        config([
            'buyers-guide.hardware' => [
                ['title' => 'Deal Test Console', 'note' => null, 'asin_us' => 'BGDLUS0001', 'asin_uk' => 'BGDLUK0001'],
                ['title' => 'Deal Test Camera', 'note' => null, 'asin_us' => 'BGDLUS0002', 'asin_uk' => null],
            ],
            'buyers-guide.games.switch-2' => [],
            'buyers-guide.games.switch-1' => [],
            'buyers-guide.deals.enabled' => true,
            'buyers-guide.deals.saving_types' => ['LIST_PRICE', 'WAS_PRICE'],
            'buyers-guide.deals.min_percent' => 10,
            'buyers-guide.uk_images' => false,
        ]);
    }

    private function apiItem(string $asin, ?string $price, ?string $was = null, ?string $type = null, ?int $percent = null, bool $map = false): array
    {
        $priceBlock = [];
        if ($price) {
            $priceBlock['money'] = ['displayAmount' => $price];
        }
        if ($was) {
            $priceBlock['savingBasis'] = ['money' => ['displayAmount' => $was], 'savingBasisType' => $type];
            $priceBlock['savings'] = ['percentage' => $percent];
        }
        $listing = ['isBuyBoxWinner' => true, 'price' => $priceBlock];
        if ($map) {
            $listing['violatesMAP'] = true;
        }

        return [
            'asin' => $asin,
            'itemInfo' => ['title' => ['displayValue' => 'Amazon title '.$asin]],
            'images' => ['primary' => ['large' => ['url' => 'https://m.media-amazon.com/images/I/'.$asin.'.jpg']]],
            'offersV2' => ['listings' => [$listing]],
        ];
    }

    private function offer(string $asin, ?string $price, ?string $was = null, ?string $type = null, ?int $percent = null, bool $map = false): array
    {
        return [
            'title' => 'Amazon title',
            'imageUrl' => 'https://m.media-amazon.com/images/I/'.$asin.'.jpg',
            'price' => $price,
            'wasPrice' => $was,
            'savingBasisType' => $type,
            'savingsPercent' => $percent,
            'violatesMap' => $map,
        ];
    }

    private function makeGame(string $title, string $usAsin): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-buyers-guide-deals-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->game_status = GameStatus::ACTIVE;
        $game->amazon_us_asin = $usAsin;
        $game->save();
        return $game;
    }

    public function testGetItemsReadsPriceSavingAndImage()
    {
        Http::fake([
            CreatorsApi::TOKEN_URL => Http::response(['access_token' => 'test-token']),
            CreatorsApi::GET_ITEMS_URL => Http::response(['itemsResult' => ['items' => [
                $this->apiItem('BGDLUS0001', '$29.44', '$59.99', 'LIST_PRICE', 51),
                $this->apiItem('BGDLUS0002', null, null, null, null, true),
            ]]]),
        ]);

        $items = app(CreatorsApi::class)->getItems(['BGDLUS0001', 'BGDLUS0002']);

        $this->assertSame('$29.44', $items['BGDLUS0001']['price']);
        $this->assertSame('$59.99', $items['BGDLUS0001']['wasPrice']);
        $this->assertSame('LIST_PRICE', $items['BGDLUS0001']['savingBasisType']);
        $this->assertSame(51, $items['BGDLUS0001']['savingsPercent']);
        $this->assertSame('https://m.media-amazon.com/images/I/BGDLUS0001.jpg', $items['BGDLUS0001']['imageUrl']);
        $this->assertTrue($items['BGDLUS0002']['violatesMap']);
        $this->assertNull($items['BGDLUS0002']['price']);
    }

    public function testRefreshBatchesTenAsinsARequestAndSavesTheOffers()
    {
        $hardware = [];
        for ($i = 1; $i <= 12; $i++) {
            $hardware[] = ['title' => 'HW '.$i, 'asin_us' => sprintf('BGBATCH%03d', $i), 'asin_uk' => null];
        }
        config(['buyers-guide.hardware' => $hardware]);

        Http::fake([
            CreatorsApi::TOKEN_URL => Http::response(['access_token' => 'test-token']),
            CreatorsApi::GET_ITEMS_URL => function (Request $request) {
                $items = array_map(fn($asin) => $this->apiItem($asin, '$10.00'), $request['itemIds']);
                return Http::response(['itemsResult' => ['items' => $items]]);
            },
        ]);

        $refresher = new OfferRefresher(app(CreatorsApi::class), app(OfferStore::class), app(\App\Domain\GameLists\Repository::class), 0);
        $result = $refresher->refresh();

        $this->assertSame(['asked' => 12, 'returned' => 12, 'errors' => []], $result);
        $batches = Http::recorded(fn($request) => $request->url() === CreatorsApi::GET_ITEMS_URL)
            ->map(fn($pair) => count($pair[0]['itemIds']))->values()->all();
        $this->assertSame([10, 2], $batches);
        $this->assertCount(12, app(OfferStore::class)->latest()['items']);
    }

    public function testRefreshKeepsEarlierOffersWhenEveryBatchFails()
    {
        app(OfferStore::class)->put(['BGDLUS0001' => $this->offer('BGDLUS0001', '$10.00')], now()->subHour());
        Http::fake([
            CreatorsApi::TOKEN_URL => Http::response(['access_token' => 'test-token']),
            CreatorsApi::GET_ITEMS_URL => Http::response(['errors' => []], 500),
        ]);

        // Artisan::call, not $this->artisan(): the mocked console output doesn't load here
        $this->assertSame(1, Artisan::call('buyers-guide:refresh-offers'));

        $this->assertArrayHasKey('BGDLUS0001', app(OfferStore::class)->latest()['items']);
    }

    public function testUsPageShowsDealsWithTimestampAndDisclaimer()
    {
        $game = $this->makeGame('Deal Test Game', 'BGDLUS0003');
        $skipped = $this->makeGame('Small Saving Game', 'BGDLUS0004');
        config(['buyers-guide.games.switch-2' => [$game->id, $skipped->id]]);

        // Travel first: the cache expiry counts from the time of the put
        $this->travelTo(\Carbon\Carbon::parse('2026-11-15 14:30:00', 'UTC'));
        app(OfferStore::class)->put([
            'BGDLUS0001' => $this->offer('BGDLUS0001', '$499.00'),
            'BGDLUS0002' => $this->offer('BGDLUS0002', '$43.99', '$49.99', 'WAS_PRICE', 12),
            'BGDLUS0003' => $this->offer('BGDLUS0003', '$29.44', '$59.99', 'LIST_PRICE', 51),
            'BGDLUS0004' => $this->offer('BGDLUS0004', '$36.49', '$38.79', 'WAS_PRICE', 6),
        ], \Carbon\Carbon::parse('2026-11-15 14:15:00', 'UTC'));

        $response = $this->get(route('buyersGuide.region', ['region' => 'us']))->assertStatus(200);

        $response->assertSeeInOrder(['Deals at Amazon US', 'Deal Test Game', '51% off', 'Deal Test Camera', '12% off', 'Consoles and accessories'])
            ->assertSee('List price: <s>$59.99</s>', false)
            ->assertSee('Was: <s>$49.99</s>', false)
            ->assertSee('Prices checked 15 November 2026, 14:15 UTC.')
            ->assertSee('Product prices and availability are accurate as of the date/time indicated and are subject to change.')
            ->assertSee('https://www.amazon.com/dp/BGDLUS0003/?tag=ssbuyersguide-20', false)
            ->assertSee('https://m.media-amazon.com/images/I/BGDLUS0001.jpg', false)
            ->assertDontSee('$36.49')
            ->assertDontSee('$499.00');
    }

    public function testMapListingsAndUnlistedSavingTypesAreNotDeals()
    {
        config(['buyers-guide.deals.saving_types' => ['LIST_PRICE']]);
        app(OfferStore::class)->put([
            'BGDLUS0001' => $this->offer('BGDLUS0001', '$400.00', '$499.00', 'LIST_PRICE', 20, true),
            'BGDLUS0002' => $this->offer('BGDLUS0002', '$43.99', '$49.99', 'WAS_PRICE', 12),
        ], now());

        $this->get(route('buyersGuide.region', ['region' => 'us']))
            ->assertStatus(200)
            ->assertDontSee('Deals at Amazon US')
            ->assertDontSee('Prices checked');
    }

    public function testNoDealsOrImagesWithoutRecentOffers()
    {
        $this->get(route('buyersGuide.region', ['region' => 'us']))
            ->assertStatus(200)
            ->assertDontSee('Deals at Amazon US')
            ->assertDontSee('m.media-amazon.com', false);
    }

    public function testDealsCanBeTurnedOff()
    {
        config(['buyers-guide.deals.enabled' => false]);
        app(OfferStore::class)->put([
            'BGDLUS0002' => $this->offer('BGDLUS0002', '$43.99', '$49.99', 'WAS_PRICE', 12),
        ], now());

        $this->get(route('buyersGuide.region', ['region' => 'us']))
            ->assertStatus(200)
            ->assertDontSee('Deals at Amazon US')
            // Images still show: they don't depend on the deals switch
            ->assertSee('https://m.media-amazon.com/images/I/BGDLUS0002.jpg', false);
    }

    public function testUkPageHasNoDealsAndReusesUsImagesOnlyWhenAllowed()
    {
        app(OfferStore::class)->put([
            'BGDLUS0001' => $this->offer('BGDLUS0001', '$400.00', '$499.00', 'LIST_PRICE', 20),
        ], now());

        $this->get(route('buyersGuide.region', ['region' => 'uk']))
            ->assertStatus(200)
            ->assertDontSee('Deals at Amazon')
            ->assertDontSee('m.media-amazon.com', false);

        config(['buyers-guide.uk_images' => true]);
        $this->get(route('buyersGuide.region', ['region' => 'uk']))
            ->assertStatus(200)
            ->assertDontSee('Deals at Amazon')
            ->assertSee('https://m.media-amazon.com/images/I/BGDLUS0001.jpg', false)
            ->assertSee('https://www.amazon.co.uk/dp/BGDLUK0001/', false);
    }
}
