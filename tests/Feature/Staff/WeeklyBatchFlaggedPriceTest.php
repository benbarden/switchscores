<?php

namespace Tests\Feature\Staff;

use App\Domain\Scraper\NintendoCoUkGameData;
use App\Domain\WeeklyBatch\FlaggedPriceResolver;
use App\Domain\WeeklyBatch\NintendoPageFetcher;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Domain\WeeklyBatchRawPage\Repository as WeeklyBatchRawPageRepository;
use App\Models\User;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Flagged weekly update prices settled from the price API: from a pasted 7001 after parsing,
 * and from the store page's 7001 SKUs after fetching. Prices are made up; the shape of the API
 * response is the one in PriceLookupTest.
 */
class WeeklyBatchFlaggedPriceTest extends TestCase
{
    use DatabaseTransactions;

    private const GAME = '70010000000001';
    private const SECOND_GAME = '70010000000002';
    private const UPGRADE = '70050000000001';
    private const BUNDLE = '70070000000001';

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2097-01-'.str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function resolver(): FlaggedPriceResolver
    {
        return app(FlaggedPriceResolver::class);
    }

    private function makeItem(array $data = []): WeeklyBatchItem
    {
        $item = new WeeklyBatchItem();
        $item->fill($data + [
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => 'new',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => 'Zqx Flagged Price',
            'title_raw' => 'Zqx Flagged Price',
            'nsuid' => self::GAME,
            'price_gbp' => 9.99,
            'price_flag' => true,
            'price_flag_reason' => 'Starting from price - validate on game page',
            'item_status' => WeeklyBatchItem::STATUS_FETCH_PENDING,
        ]);
        $item->save();
        return $item;
    }

    private function fakePrices(array $prices, string $status = 'onsale'): void
    {
        $items = [];
        foreach ($prices as $nsuid => $regular) {
            $items[] = [
                'title_id' => (int) $nsuid,
                'sales_status' => $status,
                'regular_price' => ['amount' => '£'.$regular, 'currency' => 'GBP', 'raw_value' => $regular],
            ];
        }
        Http::fake(['*' => Http::response(['prices' => $items])]);
    }

    private function assertSettled(WeeklyBatchItem $item, float $price): void
    {
        $item->refresh();
        $this->assertEquals($price, (float) $item->price_gbp);
        $this->assertFalse($item->price_flag);
        $this->assertNull($item->price_flag_reason);
    }

    private function assertStillFlagged(WeeklyBatchItem $item, float $price): void
    {
        $item->refresh();
        $this->assertEquals($price, (float) $item->price_gbp);
        $this->assertTrue($item->price_flag);
    }

    public function testAPasted7001IsSettled()
    {
        $this->fakePrices([self::GAME => '39.99']);
        $item = $this->makeItem();

        $this->assertSame(1, $this->resolver()->resolvePasted([$item]));

        $this->assertSettled($item, 39.99);
    }

    public function testAPreorderPriceIsUsed()
    {
        $this->fakePrices([self::GAME => '39.99'], 'preorder');
        $item = $this->makeItem();

        $this->resolver()->resolvePasted([$item]);

        $this->assertSettled($item, 39.99);
    }

    public function testAPastedBundleAndUpgradeKeepTheirFlags()
    {
        Http::fake();
        $bundle = $this->makeItem(['nsuid' => self::BUNDLE]);
        $upgrade = $this->makeItem(['nsuid' => self::UPGRADE]);

        $this->assertSame(0, $this->resolver()->resolvePasted([$bundle, $upgrade]));

        Http::assertNothingSent();
        $this->assertStillFlagged($bundle, 9.99);
        $this->assertStillFlagged($upgrade, 9.99);
    }

    public function testAnUnflaggedItemIsLeftAlone()
    {
        Http::fake();
        $item = $this->makeItem(['price_flag' => false, 'price_flag_reason' => null]);

        $this->resolver()->resolvePasted([$item]);

        Http::assertNothingSent();
        $this->assertEquals(9.99, (float) $item->fresh()->price_gbp);
    }

    public function testAPasted7001AtZeroWaitsForTheStorePage()
    {
        $this->fakePrices([self::GAME => '0']);
        $item = $this->makeItem(['price_gbp' => 0]);

        $this->assertSame(0, $this->resolver()->resolvePasted([$item]));

        $this->assertStillFlagged($item, 0);
    }

    public function testAFailedLookupKeepsTheFlag()
    {
        Http::fake(['*' => Http::response('', 500)]);
        $item = $this->makeItem();

        $this->assertSame(0, $this->resolver()->resolvePasted([$item]));

        $this->assertStillFlagged($item, 9.99);
    }

    public function testATerminatedSkuIsNotUsed()
    {
        $this->fakePrices([self::GAME => '39.99'], 'sales_termination');
        $item = $this->makeItem();

        $this->resolver()->resolvePasted([$item]);

        $this->assertStillFlagged($item, 9.99);
    }

    public function testAnUpgradePackGetsTheGamePriceFromTheStorePage()
    {
        $this->fakePrices([self::GAME => '39.18']);
        $item = $this->makeItem(['nsuid' => self::UPGRADE, 'price_gbp' => 7.69]);

        $this->assertTrue($this->resolver()->resolveFromStorePage($item, [self::GAME]));

        $this->assertSettled($item, 39.18);
    }

    public function testAFreeSkuNextToAPaidOneIsAFriendsPass()
    {
        $this->fakePrices([self::GAME => '0', self::SECOND_GAME => '24.99']);
        $item = $this->makeItem(['price_gbp' => 0]);

        $this->resolver()->resolveFromStorePage($item, [self::GAME, self::SECOND_GAME]);

        $this->assertSettled($item, 24.99);
    }

    public function testOnlyFreeSkusMeansTheGameIsFree()
    {
        $this->fakePrices([self::GAME => '0']);
        $item = $this->makeItem(['price_gbp' => 0]);

        $this->assertTrue($this->resolver()->resolveFromStorePage($item, [self::GAME]));

        $this->assertSettled($item, 0);
    }

    public function testPaidSkusAtDifferentPricesKeepTheFlag()
    {
        $this->fakePrices([self::GAME => '19.99', self::SECOND_GAME => '24.99']);
        $item = $this->makeItem();

        $this->assertFalse($this->resolver()->resolveFromStorePage($item, [self::GAME, self::SECOND_GAME]));

        $this->assertStillFlagged($item, 9.99);
    }

    public function testNoSkusOnThePageKeepsTheFlag()
    {
        Http::fake();
        $item = $this->makeItem();

        $this->assertFalse($this->resolver()->resolveFromStorePage($item, []));

        Http::assertNothingSent();
        $this->assertStillFlagged($item, 9.99);
    }

    public function testTheNoteSaysWhatTheApiDid()
    {
        $this->fakePrices([self::GAME => '39.18']);

        $changed = $this->makeItem(['price_gbp' => 7.69]);
        $this->resolver()->resolvePasted([$changed]);
        $this->assertSame('£7.69 → £39.18 (price API)', $changed->fresh()->price_note);

        $confirmed = $this->makeItem(['price_gbp' => 39.18]);
        $this->resolver()->resolvePasted([$confirmed]);
        $this->assertSame('£39.18 confirmed (price API)', $confirmed->fresh()->price_note);

        $missing = $this->makeItem(['price_gbp' => null]);
        $this->resolver()->resolvePasted([$missing]);
        $this->assertSame('£39.18 (price API)', $missing->fresh()->price_note);
    }

    public function testAPriceEnteredByHandClearsTheNote()
    {
        $item = $this->makeItem(['price_flag' => false, 'price_note' => '£7.69 → £39.18 (price API)']);

        app(WeeklyBatchItemRepository::class)->updatePrice($item, 35.00);

        $this->assertNull($item->fresh()->price_note);
    }

    public function testTheFetchAndPricesStepsShowTheNote()
    {
        $this->makeItem([
            'price_flag' => false,
            'price_flag_reason' => null,
            'price_note' => '£7.69 → £39.18 (price API)',
            'item_status' => WeeklyBatchItem::STATUS_CATEGORY_PENDING,
            'category' => 'zqx',
        ]);
        // The Fetch step only shows its table once the list has a page
        app(WeeklyBatchRawPageRepository::class)->saveOrReplace($this->batch->id, 'switch-2', 'new', 1, 'zqx', null);
        $owner = new User([
            'display_name' => 'Flagged Price Test',
            'email' => 'xx.flagged.price.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
        $params = ['batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'new'];

        foreach (['fetch', 'prices'] as $step) {
            $response = $this->actingAs($owner)->get(route('staff.games.weekly-updates.list.'.$step, $params));
            $response->assertStatus(200);
            $response->assertSee('£7.69 → £39.18 (price API)');
        }
    }

    public function testAPriceAboveTheLimitClearsTheLowPriceFlag()
    {
        $this->fakePrices([self::GAME => '18.89']);
        $item = $this->makeItem(['price_gbp' => 0.89, 'lq_flag' => true, 'lq_flag_reason' => 'Very low price: £0.89']);

        $this->resolver()->resolvePasted([$item]);

        $item->refresh();
        $this->assertFalse($item->lq_flag);
        $this->assertNull($item->lq_flag_reason);
    }

    public function testOtherLqReasonsStay()
    {
        $this->fakePrices([self::GAME => '3.43']);
        $item = $this->makeItem([
            'price_gbp' => 0.86,
            'lq_flag' => true,
            'lq_flag_reason' => 'Very low price: £0.86; Publisher confirmed LQ: Zqx',
        ]);

        $this->resolver()->resolvePasted([$item]);

        $item->refresh();
        $this->assertTrue($item->lq_flag);
        $this->assertSame('Publisher confirmed LQ: Zqx', $item->lq_flag_reason);
    }

    public function testAPriceStillBelowTheLimitKeepsTheLowPriceFlag()
    {
        $this->fakePrices([self::GAME => '0.79']);
        $item = $this->makeItem(['price_gbp' => 0.89, 'lq_flag' => true, 'lq_flag_reason' => 'Very low price: £0.89']);

        $this->resolver()->resolvePasted([$item]);

        $item->refresh();
        $this->assertTrue($item->lq_flag);
        $this->assertSame('Very low price: £0.89', $item->lq_flag_reason);
    }

    public function testAPriceEnteredByHandClearsTheLowPriceFlag()
    {
        $item = $this->makeItem(['price_gbp' => 0.89, 'lq_flag' => true, 'lq_flag_reason' => 'Very low price: £0.89']);

        app(WeeklyBatchItemRepository::class)->updatePrice($item, 12.99);

        $this->assertFalse($item->fresh()->lq_flag);
    }

    public function testFetchStepSkipsNeedsReviewOnceTheLowPriceIsGone()
    {
        $this->fakePrices([self::GAME => '18.89']);
        $item = $this->makeItem([
            'nsuid' => self::UPGRADE,
            'price_gbp' => 0.89,
            'lq_flag' => true,
            'lq_flag_reason' => 'Very low price: £0.89',
            'nintendo_url' => 'https://www.nintendo.com/en-gb/zqx.html',
        ]);
        $this->mock(NintendoPageFetcher::class)->shouldReceive('fetch')->andReturn([
            'publisher_raw' => 'Zqx Publisher',
            'publisher_normalised' => 'Zqx Publisher',
            'players' => '1',
            'description' => 'Zqx description',
            'lq_confirmed' => false,
            'lq_uncertain' => false,
            'lq_flag_reason' => null,
            'lq_publisher_name' => null,
            'standalone_nsuids' => [self::GAME],
        ]);
        $owner = new User([
            'display_name' => 'Flagged Price Test',
            'email' => 'xx.flagged.price.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);

        $this->actingAs($owner)->post(route('staff.games.weekly-updates.list.fetch.item', [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'new', 'itemId' => $item->id,
        ]))->assertJson(['status' => 'done']);

        $item->refresh();
        $this->assertFalse($item->lq_flag);
        $this->assertNotSame(WeeklyBatchItem::STATUS_LQ_REVIEW, $item->item_status);
    }

    public function testScraperFindsTheStandaloneSkusOnly()
    {
        $html = '<html><head><title>x</title></head><body>'
            .'<script>const nsuids = [ { nsuid: "'.self::UPGRADE.'" } ]</script>'
            .'<div data-nsuid="'.self::GAME.'"></div><div data-nsuid="'.self::GAME.'"></div>'
            .'<div data-nsuid="'.self::BUNDLE.'"></div><div data-nsuid="70020000000101"></div></body></html>';

        $this->assertSame([self::GAME], (new NintendoCoUkGameData($html))->getStandaloneNsuids());
    }

    public function testFetchStepSettlesAFlaggedItem()
    {
        $this->fakePrices([self::GAME => '39.18']);
        $item = $this->makeItem([
            'nsuid' => self::UPGRADE,
            'price_gbp' => 7.69,
            'nintendo_url' => 'https://www.nintendo.com/en-gb/zqx.html',
        ]);
        $this->mock(NintendoPageFetcher::class)->shouldReceive('fetch')->andReturn([
            'publisher_raw' => 'Zqx Publisher',
            'publisher_normalised' => 'Zqx Publisher',
            'players' => '1',
            'description' => 'Zqx description',
            'lq_confirmed' => false,
            'lq_uncertain' => false,
            'lq_flag_reason' => null,
            'lq_publisher_name' => null,
            'standalone_nsuids' => [self::GAME],
        ]);
        $owner = new User([
            'display_name' => 'Flagged Price Test',
            'email' => 'xx.flagged.price.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);

        $response = $this->actingAs($owner)->post(route('staff.games.weekly-updates.list.fetch.item', [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'new', 'itemId' => $item->id,
        ]));

        $response->assertJson(['status' => 'done', 'price_note' => '£7.69 → £39.18 (price API)']);
        $this->assertSettled($item, 39.18);
    }
}
