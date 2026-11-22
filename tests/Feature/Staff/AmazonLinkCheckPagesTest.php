<?php

namespace Tests\Feature\Staff;

use App\Domain\AffiliateCodes\UsLinkCheckSummary;
use App\Domain\AffiliateCodes\UsLinkCheckTitles;
use App\Domain\StaffDashboard\AttentionTiles;
use App\Domain\StaffDashboard\TileState;
use App\Enums\AmazonLinkCheckResult;
use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The Amazon US link check's staff pages: the list, Mark as fine, the staff home tile, and the
 * edit page clearing a result when the ASIN changes.
 */
class AmazonLinkCheckPagesTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Link Check Test',
            'email' => 'xx.link.check.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(?AmazonLinkCheckResult $result, array $fields = []): Game
    {
        $game = new Game();
        $game->title = 'Zqx Link Check '.uniqid();
        $game->link_title = 'test-link-check-'.uniqid();
        $game->console_id = Console::ID_SWITCH_1;
        $game->price_eshop = '9.99';
        $game->amazon_us_status = 'linked';
        $game->amazon_us_asin = 'B0'.strtoupper(substr(md5(uniqid()), 0, 8));
        $game->amazon_us_check_result = $result;
        $game->amazon_us_checked_at = $result ? now() : null;
        foreach ($fields as $field => $value) {
            $game->$field = $value;
        }
        $game->save();

        return $game->fresh();
    }

    private function summary(array $counts, ?string $lastCheckedAt = '2026-11-22 02:30:00'): UsLinkCheckSummary
    {
        return new class($counts, $lastCheckedAt) extends UsLinkCheckSummary {
            public function __construct(private array $fakeCounts, private ?string $fakeLast)
            {
            }

            public function build(): array
            {
                return [
                    'groups' => [], 'markedOk' => [], 'counts' => $this->fakeCounts,
                    'total' => array_sum($this->fakeCounts), 'lastCheckedAt' => $this->fakeLast,
                ];
            }
        };
    }

    private function tile(UsLinkCheckSummary $summary): array
    {
        $this->app->instance(UsLinkCheckSummary::class, $summary);

        return app(AttentionTiles::class)->amazonUsLinksTile();
    }

    public function testListShowsEachProblemInItsGroupWithAmazonsTitle()
    {
        $broken = $this->makeGame(AmazonLinkCheckResult::CONSOLE_MISMATCH);
        $outOfStock = $this->makeGame(AmazonLinkCheckResult::OUT_OF_STOCK);
        $fine = $this->makeGame(AmazonLinkCheckResult::OK);
        app(UsLinkCheckTitles::class)->put([$broken->amazon_us_asin => 'Zqx Other Console - Nintendo Switch 2']);

        $summary = app(UsLinkCheckSummary::class)->build();
        $ids = fn($group) => array_map(fn($row) => $row['game']->id, $summary['groups'][$group]);

        $this->assertContains($broken->id, $ids(AmazonLinkCheckResult::GROUP_BROKEN));
        $this->assertContains($outOfStock->id, $ids(AmazonLinkCheckResult::GROUP_CANT_BUY));
        foreach ($summary['groups'] as $rows) {
            $this->assertNotContains($fine->id, array_map(fn($row) => $row['game']->id, $rows));
        }

        $this->actingAs($this->owner())
            ->get(route('staff.games.affiliates.usLinkCheck'))
            ->assertOk()
            ->assertSee($broken->title)
            ->assertSee('Zqx Other Console - Nintendo Switch 2')
            ->assertSee($outOfStock->title)
            ->assertDontSee($fine->title)
            ->assertSee('https://www.amazon.com/dp/'.$broken->amazon_us_asin, false);

        Cache::forget(UsLinkCheckTitles::CACHE_KEY);
    }

    public function testMarkAsFineSetsTheGameAsideUntilItsAsinChanges()
    {
        $game = $this->makeGame(AmazonLinkCheckResult::REGION_EDITION);
        $markedOk = fn() => array_map(fn($row) => $row['game']->id, app(UsLinkCheckSummary::class)->build()['markedOk']);

        $this->actingAs($this->owner())
            ->post(route('staff.games.affiliates.usLinkCheck.markOk', ['gameId' => $game->id]))
            ->assertRedirect(route('staff.games.affiliates.usLinkCheck'));

        $this->assertSame($game->amazon_us_asin, $game->fresh()->amazon_us_check_ok_asin);
        $this->assertContains($game->id, $markedOk());

        // A different ASIN found to be a region edition too is back on the list
        $game = $game->fresh();
        $game->amazon_us_asin = 'B0ZQXNEW01';
        $game->save();
        $this->assertNotContains($game->id, $markedOk());

        $this->actingAs($this->owner())
            ->post(route('staff.games.affiliates.usLinkCheck.undoOk', ['gameId' => $game->id]))
            ->assertRedirect();
        $this->assertNull($game->fresh()->amazon_us_check_ok_asin);
    }

    public function testOnlyCheckByHandRowsCanBeMarkedAsFine()
    {
        // Marked as fine only sets aside Check by hand: a broken link stays on the list
        $game = $this->makeGame(AmazonLinkCheckResult::INVALID);
        $game->amazon_us_check_ok_asin = $game->amazon_us_asin;
        $game->save();

        $summary = app(UsLinkCheckSummary::class)->build();

        $this->assertContains($game->id, array_map(fn($row) => $row['game']->id, $summary['groups'][AmazonLinkCheckResult::GROUP_BROKEN]));
    }

    public function testGamesNoLongerLinkedLeaveTheList()
    {
        $game = $this->makeGame(AmazonLinkCheckResult::OUT_OF_STOCK, ['amazon_us_status' => 'no_product']);

        $summary = app(UsLinkCheckSummary::class)->build();

        $this->assertNotContains($game->id, array_map(fn($row) => $row['game']->id, $summary['groups'][AmazonLinkCheckResult::GROUP_CANT_BUY]));
    }

    public function testTileIsRedWhenALinkIsBroken()
    {
        $tile = $this->tile($this->summary(['broken' => 1, 'cant_buy' => 51, 'check_by_hand' => 6]));

        $this->assertSame(TileState::RED, $tile['state']);
        $this->assertSame(58, $tile['count']);
        $this->assertSame('1 broken, 51 can\'t buy, 6 check by hand', $tile['context']);
        $this->assertSame(route('staff.games.affiliates.usLinkCheck'), $tile['url']);
    }

    public function testTileIsAmberForOutOfStockOrCheckByHand()
    {
        $tile = $this->tile($this->summary(['broken' => 0, 'cant_buy' => 3, 'check_by_hand' => 0]));

        $this->assertSame(TileState::AMBER, $tile['state']);
        $this->assertSame('3 can\'t buy', $tile['context']);
    }

    public function testTileIsOkWithNoProblems()
    {
        $tile = $this->tile($this->summary(['broken' => 0, 'cant_buy' => 0, 'check_by_hand' => 0]));

        $this->assertSame(TileState::OK, $tile['state']);
        $this->assertSame(0, $tile['count']);
    }

    public function testTileSaysWhenTheCheckHasNeverRun()
    {
        $tile = $this->tile($this->summary(['broken' => 0, 'cant_buy' => 0, 'check_by_hand' => 0], null));

        $this->assertSame(TileState::NONE, $tile['state']);
        $this->assertSame('Not checked yet', $tile['context']);
    }

    public function testStaffHomeShowsTheTile()
    {
        $this->actingAs($this->owner())
            ->get(route('staff.index'))
            ->assertOk()
            ->assertSee('data-tile="amazon-us-links"', false);
    }

    public function testAffiliatesDashboardShowsTheCounts()
    {
        $this->actingAs($this->owner())
            ->get(route('staff.games.affiliates'))
            ->assertOk()
            ->assertSee('Amazon US link check')
            ->assertSee(route('staff.games.affiliates.usLinkCheck'), false);
    }

    public function testEditPageShowsTheLastCheckAndFindMatch()
    {
        config(['services.amazon_creators' => ['credential_id' => 'test-id', 'credential_secret' => 'test-secret']]);
        $game = $this->makeGame(AmazonLinkCheckResult::OUT_OF_STOCK);

        $this->actingAs($this->owner())
            ->get(route('staff.games.editAffiliates', ['gameId' => $game->id]))
            ->assertOk()
            ->assertSee('Last link check:')
            ->assertSee('Out of stock')
            ->assertSee('js-find-match', false)
            ->assertSee('Other link (only used when there\'s no ASIN)', false);
    }

    public function testSavingANewAsinClearsTheCheckResult()
    {
        $game = $this->makeGame(AmazonLinkCheckResult::OUT_OF_STOCK);
        $url = route('staff.games.editAffiliates', ['gameId' => $game->id]);
        $fields = [
            'amazon_uk_asin' => '', 'amazon_uk_link' => '', 'amazon_uk_status' => 'unchecked',
            'amazon_us_link' => '', 'amazon_us_status' => 'linked',
        ];

        // The same ASIN keeps its result
        $this->actingAs($this->owner())->post($url, $fields + ['amazon_us_asin' => $game->amazon_us_asin])->assertRedirect();
        $this->assertSame(AmazonLinkCheckResult::OUT_OF_STOCK, $game->fresh()->amazon_us_check_result);

        $this->actingAs($this->owner())->post($url, $fields + ['amazon_us_asin' => 'B0ZQXNEW02'])->assertRedirect();
        $game = $game->fresh();
        $this->assertSame('B0ZQXNEW02', $game->amazon_us_asin);
        $this->assertNull($game->amazon_us_check_result);
        $this->assertNull($game->amazon_us_checked_at);
    }

    public function testNoProductOrIgnoredIsSavedWithoutAnAsinOrLink()
    {
        $url = fn($game) => route('staff.games.editAffiliates', ['gameId' => $game->id]);

        foreach (['no_product', 'ignored'] as $status) {
            $game = $this->makeGame(AmazonLinkCheckResult::CONSOLE_MISMATCH, [
                'amazon_uk_asin' => 'B0ZQXUK001', 'amazon_uk_status' => 'linked',
            ]);

            // The ASIN and link sent with it are dropped: the buy button would use them otherwise
            $this->actingAs($this->owner())->post($url($game), [
                'amazon_us_asin' => $game->amazon_us_asin, 'amazon_us_link' => 'https://www.amazon.com/some-page',
                'amazon_us_status' => $status,
                'amazon_uk_asin' => 'B0ZQXUK001', 'amazon_uk_link' => '', 'amazon_uk_status' => 'linked',
            ])->assertRedirect();

            $game = $game->fresh();
            $this->assertNull($game->amazon_us_asin, $status);
            $this->assertNull($game->amazon_us_link, $status);
            $this->assertSame($status, $game->amazon_us_status);
            $this->assertNull($game->amazon_us_check_result, $status);
            // The other store is left alone
            $this->assertSame('B0ZQXUK001', $game->amazon_uk_asin, $status);
        }
    }

    public function testQuickSaveOfANewAsinClearsTheCheckResult()
    {
        $game = $this->makeGame(AmazonLinkCheckResult::INVALID);

        app(\App\Domain\Game\Repository\GameAffiliateRepository::class)->saveRegionAsin($game, 'us', 'B0ZQXNEW03');

        $this->assertNull($game->fresh()->amazon_us_check_result);
    }
}
