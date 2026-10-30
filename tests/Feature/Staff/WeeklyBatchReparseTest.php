<?php

namespace Tests\Feature\Staff;

use App\Domain\WeeklyBatch\ParseService;
use App\Domain\WeeklyBatchRawPage\Repository as WeeklyBatchRawPageRepository;
use App\Models\Console;
use App\Models\Game;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Re-parsing a page rebuilds its items from the paste, so anything done to them since has to be
 * carried over: games added from out of range, and prices settled by hand or from the price API.
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

    private function paste(string $date, string $price): void
    {
        $raw = self::TITLE."\n".self::TITLE."\n\n"
            ."Nintendo Switch 2 • ".$date." • Adventure\n\n"
            .$price."\n\n"
            ."A game.\n";

        app(WeeklyBatchRawPageRepository::class)->saveOrReplace($this->batch->id, 'switch-2', 'new', 1, $raw, null);
        app(ParseService::class)->parsePage(
            $this->batch->id, 'switch-2', 'new', 1, $this->batch->batch_date->toDateString()
        );
    }

    private function pasteOutOfRange(string $price = '£17.75'): void
    {
        $this->paste('01/01/2001', $price);
    }

    private function pasteInRange(string $price): void
    {
        $this->paste($this->batch->batch_date->format('d/m/Y'), $price);
    }

    private function item(): WeeklyBatchItem
    {
        return WeeklyBatchItem::where('batch_id', $this->batch->id)->where('title_raw', self::TITLE)->firstOrFail();
    }

    private function add(): WeeklyBatchItem
    {
        $item = $this->item();
        app(ParseService::class)->activateOutOfRangeItem($item);
        return $item->fresh();
    }

    public function testAGameNeverAddedStaysOutOfRange()
    {
        $this->pasteOutOfRange();
        $this->pasteOutOfRange();

        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $this->item()->item_status);
    }

    public function testAnAddedGameStaysInTheBatch()
    {
        $this->pasteOutOfRange();
        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $this->add()->item_status);

        $this->pasteOutOfRange();

        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $this->item()->item_status);
    }

    public function testAnAddedGameKeepsItsFetchedDetails()
    {
        $this->pasteOutOfRange();
        $item = $this->add();
        $item->fill([
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

    public function testAnAddedGameNowInTheDbIsNotAddedAgain()
    {
        $this->pasteOutOfRange();
        $this->add();

        $game = new Game();
        $game->title = self::TITLE;
        $game->link_title = 'test-reparse-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();

        $this->pasteOutOfRange();

        $item = $this->item();
        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $item->item_status);
        $this->assertEquals($game->id, $item->game_id);
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

    public function testAStillFlaggedPriceStaysFlagged()
    {
        $this->pasteInRange('£0.00');
        $this->pasteInRange('£0.00');

        $this->assertTrue($this->item()->price_flag);
    }
}
