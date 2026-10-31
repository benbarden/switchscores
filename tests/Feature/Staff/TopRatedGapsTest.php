<?php

namespace Tests\Feature\Staff;

use App\Domain\AffiliateCodes\Amazon;
use App\Domain\AffiliateCodes\AsinExtractor;
use App\Domain\AffiliateCodes\TopRatedGaps;
use App\Enums\AmazonAffiliateStatus;
use App\Models\Console;
use App\Models\Game;
use App\Models\GscPageSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Top rated gaps: the staff lists of Top rated games with no direct Amazon link, per region, their
 * row saves, and reading an ASIN from a pasted URL.
 */
class TopRatedGapsTest extends TestCase
{
    use DatabaseTransactions;

    private const YEAR = 2026;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Top Rated Gaps Test',
            'email' => 'xx.top.rated.gaps.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(int $consoleId = Console::ID_SWITCH_1, array $fields = []): Game
    {
        $game = new Game();
        $game->title = 'Zqx Gaps '.uniqid();
        $game->link_title = 'test-gaps-'.uniqid();
        $game->console_id = $consoleId;
        $game->price_eshop = '9.99';
        $game->forceFill($fields);
        $game->save();

        return $game->fresh();
    }

    private function rankAllTime(Game $game, int $rank): void
    {
        DB::table('game_rank_alltime')->insert([
            'console_id' => $game->console_id, 'game_id' => $game->id, 'game_rank' => $rank,
        ]);
    }

    private function rankYear(Game $game, int $rank): void
    {
        DB::table('game_rank_year')->insert([
            'console_id' => $game->console_id, 'release_year' => self::YEAR,
            'game_id' => $game->id, 'game_rank' => $rank,
        ]);
    }

    private function clicks(string $path, int $clicks): void
    {
        GscPageSnapshot::where('page_type', 'top_rated')->delete();
        GscPageSnapshot::create([
            'page_url' => 'https://www.switchscores.com'.$path,
            'page_type' => 'top_rated',
            'snapshot_date' => '2026-10-24',
            'window_days' => 28,
            'clicks' => $clicks,
            'impressions' => 1,
        ]);
    }

    private function gameIds(string $scope = TopRatedGaps::ALL_TIME, int $consoleId = Console::ID_SWITCH_1, string $region = 'us'): array
    {
        return app(TopRatedGaps::class)->games($region, $consoleId, $scope)->pluck('id')->all();
    }

    private function listUrl(string $scope = TopRatedGaps::ALL_TIME, string $console = 'switch-1', string $region = 'us'): string
    {
        return route('staff.games.affiliates.topRatedGaps', ['region' => $region, 'console' => $console, 'scope' => $scope]);
    }

    /**
     * @dataProvider asinValues
     */
    public function testExtractsAsin(?string $value, ?string $expected)
    {
        $this->assertSame($expected, (new AsinExtractor())->extract($value));
    }

    public static function asinValues(): array
    {
        return [
            'bare' => ['B097BP5DLQ', 'B097BP5DLQ'],
            'lower case and spaces' => ['  b097bp5dlq ', 'B097BP5DLQ'],
            'trailing slash' => ['B0D6GLYXV6/', 'B0D6GLYXV6'],
            'dp' => ['https://www.amazon.com/dp/B097BP5DLQ', 'B097BP5DLQ'],
            'title and tracking' => ['https://www.amazon.co.uk/Some-Game-Switch/dp/B097BP5DLQ/ref=sr_1_1?crid=X&keywords=a', 'B097BP5DLQ'],
            'gp product' => ['https://www.amazon.com/gp/product/B097BP5DLQ?th=1', 'B097BP5DLQ'],
            'mobile' => ['https://www.amazon.com/gp/aw/d/B097BP5DLQ/', 'B097BP5DLQ'],
            'no scheme' => ['amazon.com/dp/B097BP5DLQ', 'B097BP5DLQ'],
            'short link' => ['https://amzn.to/3abcdef', null],
            'search page' => ['https://www.amazon.com/s?k=mario', null],
            'another site' => ['https://example.com/dp/B097BP5DLQ', null],
            'too short' => ['B097BP5', null],
            'blank' => ['', null],
            'null' => [null, null],
        ];
    }

    public function testSearchUrlUsesTheGamesConsole()
    {
        $amazon = new Amazon();

        $this->assertStringContainsString('nintendo+switch+2+games', $amazon->staffSearchUrl(new Game(['console_id' => 2, 'title' => 'A']), 'us'));
        $this->assertStringContainsString('www.amazon.co.uk', $amazon->staffSearchUrl(new Game(['console_id' => 1, 'title' => 'A']), 'uk'));
        $this->assertStringNotContainsString('tag=', $amazon->staffSearchUrl(new Game(['console_id' => 1, 'title' => 'A']), 'us'));
    }

    public function testListsOnlyUncheckedGamesWithNoDirectLink()
    {
        $gap = $this->makeGame();
        $withAsin = $this->makeGame(Console::ID_SWITCH_1, ['amazon_us_asin' => 'B097BP5DLQ']);
        $withLink = $this->makeGame(Console::ID_SWITCH_1, ['amazon_us_link' => 'https://www.amazon.com/dp/B097BP5DLQ']);
        $noProduct = $this->makeGame(Console::ID_SWITCH_1, ['amazon_us_status' => AmazonAffiliateStatus::NO_PRODUCT->value]);

        foreach ([$gap, $withAsin, $withLink, $noProduct] as $game) {
            $this->rankAllTime($game, 1);
        }

        $ids = $this->gameIds();

        $this->assertContains($gap->id, $ids);
        $this->assertNotContains($withAsin->id, $ids);
        $this->assertNotContains($withLink->id, $ids);
        $this->assertNotContains($noProduct->id, $ids);
    }

    public function testEachRegionHasItsOwnGaps()
    {
        $ukGapOnly = $this->makeGame(Console::ID_SWITCH_1, ['amazon_us_asin' => 'B097BP5DLQ']);
        $usGapOnly = $this->makeGame(Console::ID_SWITCH_1, ['amazon_uk_status' => AmazonAffiliateStatus::NO_PRODUCT->value]);

        $this->rankAllTime($ukGapOnly, 1);
        $this->rankAllTime($usGapOnly, 2);

        $us = $this->gameIds(TopRatedGaps::ALL_TIME, Console::ID_SWITCH_1, 'us');
        $uk = $this->gameIds(TopRatedGaps::ALL_TIME, Console::ID_SWITCH_1, 'uk');

        $this->assertContains($usGapOnly->id, $us);
        $this->assertNotContains($ukGapOnly->id, $us);
        $this->assertContains($ukGapOnly->id, $uk);
        $this->assertNotContains($usGapOnly->id, $uk);
    }

    public function testRegionPageOnlyShowsThatRegion()
    {
        $this->rankAllTime($this->makeGame(), 1);

        $this->actingAs($this->owner())->get($this->listUrl(TopRatedGaps::ALL_TIME, 'switch-1', 'uk'))
            ->assertStatus(200)
            ->assertSee('Amazon UK')
            ->assertSee('www.amazon.co.uk/s?', false)
            ->assertDontSee('Amazon US')
            ->assertDontSee('www.amazon.com/s?', false);
    }

    public function testBreadcrumbLinksToAffiliates()
    {
        $this->actingAs($this->owner())->get($this->listUrl())
            ->assertSeeInOrder(['Staff', 'Games', route('staff.games.affiliates'), 'Affiliates', 'Top rated gaps'], false);
    }

    public function testListIsLimitedToItsPageAndTopRanks()
    {
        $yearGame = $this->makeGame();
        $allTimeGame = $this->makeGame();
        $otherConsole = $this->makeGame(Console::ID_SWITCH_2);
        $tooLow = $this->makeGame();

        $this->rankYear($yearGame, 5);
        $this->rankAllTime($allTimeGame, 1);
        $this->rankYear($otherConsole, 5);
        $this->rankYear($tooLow, 101);

        $ids = $this->gameIds((string) self::YEAR);

        $this->assertContains($yearGame->id, $ids);
        $this->assertNotContains($allTimeGame->id, $ids);
        $this->assertNotContains($otherConsole->id, $ids);
        $this->assertNotContains($tooLow->id, $ids);
    }

    public function testOverviewCountsMatchTheList()
    {
        $this->rankYear($this->makeGame(), 5);
        $this->rankAllTime($this->makeGame(Console::ID_SWITCH_2), 1);

        $overview = array_column(app(TopRatedGaps::class)->overview('us'), null, 'scope');

        $this->assertSame('All-time', array_values($overview)[0]['label']);
        $this->assertSame(
            count($this->gameIds((string) self::YEAR)),
            $overview[(string) self::YEAR]['counts'][Console::ID_SWITCH_1]
        );
        $this->assertSame(
            count($this->gameIds(TopRatedGaps::ALL_TIME, Console::ID_SWITCH_2)),
            $overview[TopRatedGaps::ALL_TIME]['counts'][Console::ID_SWITCH_2]
        );
    }

    public function testProgressCountsAGameOnTwoPagesOnce()
    {
        $service = app(TopRatedGaps::class);
        $before = $service->progress('us')[Console::ID_SWITCH_1] ?? ['done' => 0, 'total' => 0];

        $gap = $this->makeGame();
        $checked = $this->makeGame(Console::ID_SWITCH_1, ['amazon_us_status' => AmazonAffiliateStatus::NO_PRODUCT->value]);
        $notTopRated = $this->makeGame();

        $this->rankAllTime($gap, 1);
        $this->rankYear($gap, 1);
        $this->rankAllTime($checked, 2);
        $this->rankAllTime($notTopRated, 101);

        $after = $service->progress('us')[Console::ID_SWITCH_1];

        $this->assertSame($before['total'] + 2, $after['total']);
        $this->assertSame($before['done'] + 1, $after['done']);
    }

    public function testAffiliatesPageShowsProgressPerConsole()
    {
        $this->rankAllTime($this->makeGame(), 1);
        $progress = app(TopRatedGaps::class)->progress('us')[Console::ID_SWITCH_1];

        $this->actingAs($this->owner())->get(route('staff.games.affiliates'))
            ->assertStatus(200)
            ->assertSee('Switch 1 games checked')
            ->assertSee($progress['done'].' / '.$progress['total'].' ('.$progress['percentage'].'%)');
    }

    public function testPageShowsTheGameAndItsStats()
    {
        $game = $this->makeGame();
        $this->rankAllTime($game, 1);
        $this->clicks('/c/switch-1/top-rated/all-time', 384);

        $response = $this->actingAs($this->owner())->get($this->listUrl());

        $response->assertStatus(200);
        $response->assertSee($game->title);
        $response->assertSee('384 in 28 days to 2026-10-24');
        $response->assertSee(route('staff.games.affiliates.quickSave', ['gameId' => $game->id]), false);
        $response->assertSee('Not found');
    }

    public function testPageSaysSoWhenTheSnapshotHasNoClicks()
    {
        $this->clicks('/c/switch-1/top-rated/all-time', 384);

        $this->actingAs($this->owner())->get($this->listUrl(TopRatedGaps::ALL_TIME, 'switch-2'))
            ->assertStatus(200)
            ->assertSee('No data in the latest snapshot');
    }

    public function testUnknownScopeIs404()
    {
        $this->actingAs($this->owner())->get($this->listUrl('1999'))->assertStatus(404);
        $this->actingAs($this->owner())->get($this->listUrl('banana'))->assertStatus(404);
        $this->actingAs($this->owner())->get($this->listUrl(TopRatedGaps::ALL_TIME, 'switch-1', 'de'))->assertStatus(404);
    }

    public function testAffiliatesPageLinksToTheLists()
    {
        $this->rankAllTime($this->makeGame(), 1);

        $this->actingAs($this->owner())->get(route('staff.games.affiliates'))
            ->assertStatus(200)
            ->assertSee('Top rated gaps')
            ->assertSee($this->listUrl(), false);
    }

    public function testAmazonListsLinkToTheEditPage()
    {
        $game = $this->makeGame();

        $this->actingAs($this->owner())
            ->get(route('staff.games.list.showList', ['listType' => 'amazon-us-unchecked']))
            ->assertStatus(200)
            ->assertSee('Edit affiliates');
    }

    public function testQuickSaveReadsAUrlAndLeavesTheOtherRegionAlone()
    {
        $game = $this->makeGame(Console::ID_SWITCH_1, ['amazon_uk_asin' => 'B0AAAAAAAA']);

        $this->actingAs($this->owner())
            ->postJson(route('staff.games.affiliates.quickSave', ['gameId' => $game->id]), [
                'region' => 'us',
                'asin' => 'https://www.amazon.com/Some-Game/dp/B097BP5DLQ/ref=sr_1_1',
            ])
            ->assertOk()
            ->assertJson(['asin' => 'B097BP5DLQ', 'statusLabel' => 'Linked']);

        $game->refresh();
        $this->assertSame('B097BP5DLQ', $game->amazon_us_asin);
        $this->assertSame(AmazonAffiliateStatus::LINKED->value, $game->amazon_us_status);
        $this->assertSame('B0AAAAAAAA', $game->amazon_uk_asin);
    }

    public function testQuickSaveMarksNoProduct()
    {
        $game = $this->makeGame();

        $this->actingAs($this->owner())
            ->postJson(route('staff.games.affiliates.quickSave', ['gameId' => $game->id]), [
                'region' => 'uk', 'no_product' => 1,
            ])
            ->assertOk();

        $game->refresh();
        $this->assertSame(AmazonAffiliateStatus::NO_PRODUCT->value, $game->amazon_uk_status);
        $this->assertSame(AmazonAffiliateStatus::UNCHECKED->value, $game->amazon_us_status);
    }

    public function testQuickSaveWontReplaceALink()
    {
        $game = $this->makeGame(Console::ID_SWITCH_1, ['amazon_us_asin' => 'B0AAAAAAAA']);

        foreach ([['asin' => 'B097BP5DLQ'], ['no_product' => 1]] as $input) {
            $this->actingAs($this->owner())
                ->postJson(route('staff.games.affiliates.quickSave', ['gameId' => $game->id]), $input + ['region' => 'us'])
                ->assertStatus(409);
        }

        $this->assertSame('B0AAAAAAAA', $game->fresh()->amazon_us_asin);
    }

    public function testQuickSaveRejectsWhatItCantRead()
    {
        $game = $this->makeGame();
        $url = route('staff.games.affiliates.quickSave', ['gameId' => $game->id]);

        $this->actingAs($this->owner())->postJson($url, ['region' => 'us', 'asin' => 'https://amzn.to/3abcdef'])->assertStatus(422);
        $this->actingAs($this->owner())->postJson($url, ['region' => 'de', 'asin' => 'B097BP5DLQ'])->assertStatus(422);

        $this->assertNull($game->fresh()->amazon_us_asin);
    }

    /**
     * @dataProvider storeUrls
     */
    public function testKnowsAWrongStoreUrl(string $value, string $region, bool $expected)
    {
        $this->assertSame($expected, (new AsinExtractor())->isWrongStore($value, $region));
    }

    public static function storeUrls(): array
    {
        return [
            'US on US' => ['https://www.amazon.com/dp/B097BP5DLQ', 'us', false],
            'UK on UK' => ['https://www.amazon.co.uk/dp/B097BP5DLQ', 'uk', false],
            'UK on US' => ['https://www.amazon.co.uk/dp/B075ND55T3?ar_mt=BEST_BET', 'us', true],
            'US on UK' => ['https://www.amazon.com/dp/B097BP5DLQ', 'uk', true],
            'Germany on US' => ['https://www.amazon.de/dp/B097BP5DLQ', 'us', true],
            'no scheme' => ['amazon.co.uk/dp/B097BP5DLQ', 'us', true],
            'bare ASIN' => ['B097BP5DLQ', 'us', false],
        ];
    }

    public function testRedirectedUrlIsNotSaved()
    {
        $game = $this->makeGame();

        $this->actingAs($this->owner())
            ->postJson(route('staff.games.affiliates.quickSave', ['gameId' => $game->id]), [
                'region' => 'us',
                'asin' => 'https://www.amazon.co.uk/dp/B075ND55T3?ar_srct=T1&ar_mt=BEST_BET&th=1',
            ])
            ->assertStatus(422)
            ->assertJsonPath('error', 'That URL is not from Amazon US. Check you are on the right store and the right game.');

        $this->assertNull($game->fresh()->amazon_us_asin);
    }

    public function testEditPageRefusesAWrongStoreUrl()
    {
        $game = $this->makeGame();

        $this->actingAs($this->owner())
            ->post(route('staff.games.editAffiliates', ['gameId' => $game->id]), [
                'amazon_uk_asin' => 'https://www.amazon.com/dp/B097BP5DLQ',
            ])
            ->assertSessionHasErrors('amazon_uk_asin');

        $this->assertNull($game->fresh()->amazon_uk_asin);
    }

    public function testQuickSaveIsStaffOnly()
    {
        $game = $this->makeGame();

        $response = $this->postJson(route('staff.games.affiliates.quickSave', ['gameId' => $game->id]), [
            'region' => 'us', 'asin' => 'B097BP5DLQ',
        ]);

        $this->assertNotSame(200, $response->getStatusCode());
        $this->assertNull($game->fresh()->amazon_us_asin);
    }

    public function testEditPageAcceptsAUrlAndRejectsWhatItCantRead()
    {
        $game = $this->makeGame();
        $url = route('staff.games.editAffiliates', ['gameId' => $game->id]);
        $fields = [
            'amazon_uk_asin' => '', 'amazon_uk_link' => '', 'amazon_uk_status' => 'unchecked',
            'amazon_us_link' => '', 'amazon_us_status' => 'linked',
        ];

        $this->actingAs($this->owner())
            ->post($url, $fields + ['amazon_us_asin' => 'https://www.amazon.com/dp/B097BP5DLQ?th=1'])
            ->assertRedirect();
        $this->assertSame('B097BP5DLQ', $game->fresh()->amazon_us_asin);

        $this->actingAs($this->owner())
            ->post($url, $fields + ['amazon_us_asin' => 'https://amzn.to/3abcdef'])
            ->assertSessionHasErrors('amazon_us_asin');
        $this->assertSame('B097BP5DLQ', $game->fresh()->amazon_us_asin);
    }
}
