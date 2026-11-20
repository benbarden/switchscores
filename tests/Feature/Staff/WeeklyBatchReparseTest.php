<?php

namespace Tests\Feature\Staff;

use App\Domain\WeeklyBatch\ParseService;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Domain\WeeklyBatchRawPage\Repository as WeeklyBatchRawPageRepository;
use App\Models\Console;
use App\Models\Game;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Parsing and re-parsing a page. On New lists, games outside the batch dates that aren't in the
 * DB join the batch, unless they were skipped in an earlier batch. Upcoming lists leave out games
 * with no exact date. A re-parse rebuilds the items from the paste, so anything done to them since
 * has to be carried over, including prices settled by hand or from the price API.
 * Plain text pastes have no NSUID or URL, so these tests make no price API calls.
 */
class WeeklyBatchReparseTest extends TestCase
{
    use DatabaseTransactions;

    private const TITLE = 'Zqx Reparse Game';

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2096-03-'.str_pad((string) random_int(10, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function paste(string $date, string $price, string $listType = 'new'): array
    {
        $raw = self::TITLE."\n".self::TITLE."\n\n"
            ."Nintendo Switch 2 • ".$date." • Adventure\n\n"
            .$price."\n\n"
            ."A game.\n";

        app(WeeklyBatchRawPageRepository::class)->saveOrReplace($this->batch->id, 'switch-2', $listType, 1, $raw, null);
        return app(ParseService::class)->parsePage(
            $this->batch->id, 'switch-2', $listType, 1, $this->batch->batch_date->toDateString()
        );
    }

    private function pasteOutOfRange(string $price = '£17.75'): array
    {
        return $this->paste('01/01/2001', $price);
    }

    private function pasteInRange(string $price): void
    {
        $this->paste($this->batch->batch_date->format('d/m/Y'), $price);
    }

    private function item(): WeeklyBatchItem
    {
        return WeeklyBatchItem::where('batch_id', $this->batch->id)->where('title_raw', self::TITLE)->firstOrFail();
    }

    private function makeGame(): Game
    {
        $game = new Game();
        $game->title = self::TITLE;
        $game->link_title = 'test-reparse-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();
        return $game;
    }

    private function skippedInAnEarlierBatch(string $status): void
    {
        $earlier = WeeklyBatch::create([
            'batch_date' => $this->batch->batch_date->copy()->subDays(28)->toDateString(),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
        WeeklyBatchItem::create([
            'batch_id' => $earlier->id,
            'console' => 'switch-2',
            'list_type' => 'new',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => self::TITLE,
            'title_raw' => self::TITLE,
            'item_status' => $status,
        ]);
    }

    public function testAGameFromAnEarlierWeekJoinsTheBatch()
    {
        $summary = $this->pasteOutOfRange();

        $item = $this->item();
        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $item->item_status);
        $this->assertNull($item->game_id);
        $this->assertSame(1, $summary['added']);
    }

    public function testAGameFromAnEarlierWeekStaysInTheBatchOnReparse()
    {
        $this->pasteOutOfRange();
        $this->pasteOutOfRange();

        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $this->item()->item_status);
    }

    public function testAGameFromAnEarlierWeekKeepsItsFetchedDetails()
    {
        $this->pasteOutOfRange();
        $this->item()->fill([
            'nintendo_url' => 'https://www.nintendo.com/en-gb/zqx.html',
            'fetch_status' => WeeklyBatchItem::FETCH_STATUS_FETCHED,
            'publisher_normalised' => 'Zqx Publisher',
            'item_status' => WeeklyBatchItem::STATUS_CATEGORY_PENDING,
        ])->save();

        $this->pasteOutOfRange();

        $item = $this->item();
        $this->assertSame(WeeklyBatchItem::STATUS_CATEGORY_PENDING, $item->item_status);
        $this->assertSame('Zqx Publisher', $item->publisher_normalised);
    }

    public function testAGameFromAnEarlierWeekInTheDbKeepsItsOwnStatus()
    {
        $this->pasteOutOfRange();
        $game = $this->makeGame();

        $summary = $this->pasteOutOfRange();

        $item = $this->item();
        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $item->item_status);
        $this->assertEquals($game->id, $item->game_id);
        $this->assertSame(1, $summary['already_in_db']);
    }

    public function testAGameSkippedInAnEarlierBatchComesInWithThatStatus()
    {
        $this->skippedInAnEarlierBatch(WeeklyBatchItem::STATUS_LOW_QUALITY);

        $summary = $this->pasteOutOfRange();

        $this->assertSame(WeeklyBatchItem::STATUS_LOW_QUALITY, $this->item()->item_status);
        $this->assertSame(1, $summary['skipped']);
    }

    public function testAResetGameStaysInTheBatchOnReparse()
    {
        $this->skippedInAnEarlierBatch(WeeklyBatchItem::STATUS_BUNDLE);
        $this->pasteOutOfRange();
        $this->assertSame(WeeklyBatchItem::STATUS_BUNDLE, $this->item()->item_status);

        app(WeeklyBatchItemRepository::class)->resetItem($this->item());
        $this->pasteOutOfRange();

        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $this->item()->item_status);
    }

    public function testAnUpcomingGameWithNoExactDateIsNotAdded()
    {
        $summary = $this->paste('2027', '', 'upcoming');

        $item = $this->item();
        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $item->item_status);
        $this->assertNull($item->release_date);
        $this->assertSame('2027', $item->release_date_raw);
        $this->assertSame(1, $summary['not_added']);
    }

    public function testAnUpcomingGameWithAFullDateIsAdded()
    {
        $date = $this->batch->batch_date->copy()->addYear()->format('d/m/Y');

        $this->paste($date, '£17.75', 'upcoming');

        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $this->item()->item_status);
    }

    public function testASettledPriceSurvivesOnAFetchedItem()
    {
        $this->pasteInRange('£0.00');
        $item = $this->item();
        $this->assertTrue($item->price_flag);
        $item->fill([
            'nintendo_url' => 'https://www.nintendo.com/en-gb/zqx.html',
            'item_status' => WeeklyBatchItem::STATUS_CATEGORY_PENDING,
            'price_gbp' => 39.18,
            'price_flag' => false,
            'price_flag_reason' => null,
            'price_note' => '£0.00 → £39.18 (price API)',
        ])->save();

        $this->pasteInRange('£0.00');

        $item = $this->item();
        $this->assertEquals(39.18, (float) $item->price_gbp);
        $this->assertFalse($item->price_flag);
        $this->assertSame('£0.00 → £39.18 (price API)', $item->price_note);
    }

    public function testAPriceEnteredByHandSurvivesOnAnItemWithNoUrl()
    {
        $this->pasteInRange('£0.00');
        $item = $this->item();
        $item->fill(['price_gbp' => 12.99, 'price_flag' => false, 'price_flag_reason' => null])->save();

        $this->pasteInRange('£0.00');

        $item = $this->item();
        $this->assertEquals(12.99, (float) $item->price_gbp);
        $this->assertFalse($item->price_flag);
    }

    public function testASettledPriceDoesNotBringBackTheLowPriceFlag()
    {
        $this->pasteInRange('Starting from: £0.89');
        $item = $this->item();
        $this->assertTrue($item->price_flag);
        $this->assertTrue($item->lq_flag);
        $item->fill([
            'price_gbp' => 18.89,
            'price_flag' => false,
            'price_flag_reason' => null,
            'lq_flag' => false,
            'lq_flag_reason' => null,
        ])->save();

        $this->pasteInRange('Starting from: £0.89');

        $this->assertFalse($this->item()->lq_flag);
    }

    public function testAStillFlaggedPriceStaysFlagged()
    {
        $this->pasteInRange('£0.00');
        $this->pasteInRange('£0.00');

        $this->assertTrue($this->item()->price_flag);
    }
}
