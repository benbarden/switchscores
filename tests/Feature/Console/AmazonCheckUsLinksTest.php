<?php

namespace Tests\Feature\Console;

use App\Domain\AffiliateCodes\CreatorsApi;
use App\Domain\AffiliateCodes\ProductTitleCheck;
use App\Domain\AffiliateCodes\UsLinkChecker;
use App\Enums\AmazonLinkCheckResult;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The Amazon US link check: what Amazon's getItems response means for each linked game. Every
 * Amazon call is faked, and the games aren't saved.
 */
class AmazonCheckUsLinksTest extends TestCase
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

    private function checker(): UsLinkChecker
    {
        return new UsLinkChecker(app(CreatorsApi::class), new ProductTitleCheck(), 0, 0);
    }

    private function game(int $id, string $asin, int $consoleId = Console::ID_SWITCH_1): Game
    {
        $game = new Game();
        $game->forceFill([
            'id' => $id,
            'title' => 'Zqx Link Check '.$id,
            'console_id' => $consoleId,
            'amazon_us_asin' => $asin,
        ]);

        return $game;
    }

    private function item(string $asin, string $title, array $availability): array
    {
        $listings = [];
        foreach ($availability as $type) {
            $listings[] = ['availability' => ['type' => $type, 'message' => '']];
        }

        return [
            'asin' => $asin,
            'itemInfo' => ['title' => ['displayValue' => $title]],
            'offersV2' => ['listings' => $listings],
        ];
    }

    private function fakeGetItems(array $items, array $errors = [], int $status = 200): void
    {
        Http::fake([
            CreatorsApi::TOKEN_URL => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            CreatorsApi::GET_ITEMS_URL => Http::response(
                $status === 200 ? ['itemsResult' => ['items' => $items], 'errors' => $errors] : [],
                $status
            ),
        ]);
    }

    public function testCheckItemsReturnsAvailabilityAndNamesTheFailedAsins()
    {
        $this->fakeGetItems(
            [$this->item('B0CHECK001', 'Zqx Game - Nintendo Switch', ['IN_STOCK', 'OUT_OF_STOCK'])],
            [
                ['code' => 'ItemNotAccessible', 'message' => 'The ItemId B0CHECK002 is not accessible through the Product Advertising API.'],
                ['code' => 'InvalidParameterValue', 'message' => 'The ItemIds B0CHECK003 provided in the request is invalid.'],
            ]
        );

        $response = app(CreatorsApi::class)->checkItems(['B0CHECK001', 'B0CHECK002', 'B0CHECK003', 'B0CHECK004']);

        $this->assertSame(['title' => 'Zqx Game - Nintendo Switch', 'availability' => ['IN_STOCK', 'OUT_OF_STOCK']], $response['items']['B0CHECK001']);
        $this->assertSame([
            'B0CHECK002' => 'ItemNotAccessible',
            'B0CHECK003' => 'InvalidParameterValue',
            'B0CHECK004' => 'NotReturned',
        ], $response['errors']);

        Http::assertSent(function (Request $request) {
            return $request->url() === CreatorsApi::GET_ITEMS_URL
                && in_array('offersV2.listings.availability', $request['resources'], true);
        });
    }

    public function testEachGameGetsAResult()
    {
        $this->fakeGetItems(
            [
                $this->item('B0CHECK001', 'Fine Game - Nintendo Switch', ['IN_STOCK']),
                $this->item('B0CHECK002', 'Gone Game - Nintendo Switch', ['OUT_OF_STOCK']),
                $this->item('B0CHECK003', 'No Sellers - Nintendo Switch', []),
                $this->item('B0CHECK004', 'Other Console - Nintendo Switch 2', ['IN_STOCK']),
                $this->item('B0CHECK005', 'Import Game (Non-US Version)', ['IN_STOCK_SCARCE']),
                $this->item('B0CHECK006', 'Coming Soon - Nintendo Switch™ 2', ['PREORDER']),
            ],
            [
                ['code' => 'ItemNotAccessible', 'message' => 'The ItemId B0CHECK007 is not accessible through the Product Advertising API.'],
                ['code' => 'InvalidParameterValue', 'message' => 'The ItemIds B0CHECK008 provided in the request is invalid.'],
            ]
        );

        $check = $this->checker()->check([
            $this->game(1, 'B0CHECK001'),
            $this->game(2, 'B0CHECK002'),
            $this->game(3, 'B0CHECK003'),
            $this->game(4, 'B0CHECK004'),
            $this->game(5, 'B0CHECK005'),
            $this->game(6, 'B0CHECK006', Console::ID_SWITCH_2),
            $this->game(7, 'B0CHECK007'),
            $this->game(8, 'B0CHECK008'),
        ]);

        $results = array_map(fn($row) => $row['result'], $check['results']);

        $this->assertSame([
            1 => AmazonLinkCheckResult::OK,
            2 => AmazonLinkCheckResult::OUT_OF_STOCK,
            3 => AmazonLinkCheckResult::OUT_OF_STOCK,
            4 => AmazonLinkCheckResult::CONSOLE_MISMATCH,
            5 => AmazonLinkCheckResult::REGION_EDITION,
            6 => AmazonLinkCheckResult::OK,
            7 => AmazonLinkCheckResult::NOT_ACCESSIBLE,
            8 => AmazonLinkCheckResult::INVALID,
        ], $results);
        $this->assertSame(8, $check['asked']);
        $this->assertSame([], $check['failedBatches']);
    }

    public function testWrongConsoleOutranksOutOfStock()
    {
        $this->fakeGetItems([$this->item('B0CHECK001', 'Other Console - Nintendo Switch 2', ['OUT_OF_STOCK'])]);

        $check = $this->checker()->check([$this->game(1, 'B0CHECK001')]);

        $this->assertSame(AmazonLinkCheckResult::CONSOLE_MISMATCH, $check['results'][1]['result']);
    }

    public function testGamesSharingAnAsinAreEachChecked()
    {
        $this->fakeGetItems([$this->item('B0CHECK001', 'Collection - Nintendo Switch', ['IN_STOCK'])]);

        $check = $this->checker()->check([$this->game(1, 'B0CHECK001'), $this->game(2, 'B0CHECK001')]);

        $this->assertSame(1, $check['asked']);
        $this->assertCount(2, $check['results']);
        Http::assertSentCount(2); // the token, then one getItems
    }

    public function testAsinsGoTenToARequest()
    {
        $this->fakeGetItems([]);

        $games = [];
        for ($i = 1; $i <= 23; $i++) {
            $games[] = $this->game($i, sprintf('B0CHECK%03d', $i));
        }
        $this->checker()->check($games);

        $getItems = Http::recorded(fn(Request $request) => $request->url() === CreatorsApi::GET_ITEMS_URL)->values();
        $this->assertCount(3, $getItems);
        $this->assertCount(10, $getItems[0][0]['itemIds']);
        $this->assertCount(3, $getItems[2][0]['itemIds']);
    }

    public function testTooManyRequestsIsRetried()
    {
        Http::fake([
            CreatorsApi::TOKEN_URL => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            CreatorsApi::GET_ITEMS_URL => Http::sequence()
                ->push([], 429)
                ->push(['itemsResult' => ['items' => [$this->item('B0CHECK001', 'Fine Game - Nintendo Switch', ['IN_STOCK'])]]]),
        ]);

        $check = $this->checker()->check([$this->game(1, 'B0CHECK001')]);

        $this->assertSame(AmazonLinkCheckResult::OK, $check['results'][1]['result']);
        $this->assertSame([], $check['failedBatches']);
    }

    public function testARefusedBatchIsReportedAndItsGamesLeftUnchecked()
    {
        $this->fakeGetItems([], [], 500);

        $check = $this->checker()->check([$this->game(1, 'B0CHECK001')]);

        $this->assertSame([], $check['results']);
        $this->assertCount(1, $check['failedBatches']);
    }

    public function testTooManyRequestsGivesUpAfterTheRetries()
    {
        $this->fakeGetItems([], [], 429);

        $check = $this->checker()->check([$this->game(1, 'B0CHECK001')]);

        $this->assertSame([], $check['results']);
        $this->assertCount(1, $check['failedBatches']);
        // The first try and three retries
        $this->assertCount(4, Http::recorded(fn(Request $request) => $request->url() === CreatorsApi::GET_ITEMS_URL));
    }

    public function testCheckItemsTakesAtMostTenAsins()
    {
        $this->expectException(\InvalidArgumentException::class);

        app(CreatorsApi::class)->checkItems(array_fill(0, 11, 'B0CHECK001'));
    }

    public function testResultsAreSavedWithoutAnAuditRowOrANewUpdatedAt()
    {
        $game = new Game();
        $game->title = 'Zqx Link Check Save '.uniqid();
        $game->link_title = 'test-link-check-'.uniqid();
        $game->console_id = Console::ID_SWITCH_1;
        $game->price_eshop = '9.99';
        $game->save();
        $game = $game->fresh();
        $updatedAt = (string) $game->updated_at;
        $audits = $game->audits()->count();

        $checkedAt = now()->addDay()->startOfSecond();
        app(\App\Domain\Game\Repository\AmazonLinkCheckRepository::class)->saveUsCheckResults(
            [AmazonLinkCheckResult::OUT_OF_STOCK->value => [$game->id]],
            $checkedAt
        );

        $game = $game->fresh();
        $this->assertSame(AmazonLinkCheckResult::OUT_OF_STOCK, $game->amazon_us_check_result);
        $this->assertSame($checkedAt->toDateTimeString(), $game->amazon_us_checked_at->toDateTimeString());
        $this->assertSame($updatedAt, (string) $game->updated_at);
        $this->assertSame($audits, $game->audits()->count());
    }

    public function testCommandWithoutCredentialsChecksNothing()
    {
        config(['services.amazon_creators.credential_id' => null]);
        Http::fake();

        // Artisan::call, not $this->artisan(): the mocked console output doesn't load here
        $this->assertSame(0, Artisan::call('amazon:check-us-links', ['--dry-run' => true]));
        $this->assertStringContainsString('credentials are not set', Artisan::output());

        Http::assertNothingSent();
    }
}
