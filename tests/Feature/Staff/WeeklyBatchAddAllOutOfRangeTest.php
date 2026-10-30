<?php

namespace Tests\Feature\Staff;

use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * "Add all to batch" on the Raw screen: adds the out-of-range games that aren't in the DB, but
 * not titles skipped on purpose in an earlier batch.
 */
class WeeklyBatchAddAllOutOfRangeTest extends TestCase
{
    use DatabaseTransactions;

    private WeeklyBatch $batch;
    private WeeklyBatch $earlierBatch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; far-future dates can't clash with a real batch
        $day = str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT);
        $this->batch = WeeklyBatch::create(['batch_date' => '2098-06-'.$day, 'status' => WeeklyBatch::STATUS_SETUP]);
        $this->earlierBatch = WeeklyBatch::create(['batch_date' => '2098-05-'.$day, 'status' => WeeklyBatch::STATUS_SETUP]);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Add All Test',
            'email' => 'xx.add.all.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeItem(string $title, array $data = []): WeeklyBatchItem
    {
        $item = new WeeklyBatchItem();
        $item->fill($data + [
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => 'new',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => $title,
            'title_raw' => $title,
            'item_status' => WeeklyBatchItem::STATUS_OUT_OF_RANGE,
        ]);
        $item->save();
        return $item;
    }

    private function skippedEarlier(string $title, string $status): WeeklyBatchItem
    {
        return $this->makeItem($title, ['batch_id' => $this->earlierBatch->id, 'item_status' => $status]);
    }

    private function makeGame(): Game
    {
        $game = new Game();
        $game->title = 'Zqx Add All In DB';
        $game->link_title = 'test-add-all-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();
        return $game;
    }

    private function listUrl(string $name): string
    {
        return route('staff.games.weekly-updates.list.'.$name, [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'new',
        ]);
    }

    public function testAddsTheYellowRowsOnly()
    {
        $yellow = $this->makeItem('Zqx Add All Yellow');
        $secondYellow = $this->makeItem('Zqx Add All Yellow Two', ['nintendo_url' => 'https://www.nintendo.com/en-gb/zqx.html']);
        $grey = $this->makeItem('Zqx Add All Grey');
        $this->skippedEarlier('Zqx Add All Grey', WeeklyBatchItem::STATUS_LOW_QUALITY);
        $inDb = $this->makeItem('Zqx Add All In DB', ['game_id' => $this->makeGame()->id]);
        $otherList = $this->makeItem('Zqx Add All Other List', ['list_type' => 'upcoming']);

        $response = $this->actingAs($this->owner())->post($this->listUrl('raw.activate-all-out-of-range'));

        $response->assertRedirect($this->listUrl('raw'));
        $response->assertSessionHas('success', '2 game(s) added to this batch.');
        $this->assertSame(WeeklyBatchItem::STATUS_PENDING, $yellow->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_FETCH_PENDING, $secondYellow->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $grey->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $inDb->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_OUT_OF_RANGE, $otherList->fresh()->item_status);
    }

    public function testRawScreenShowsTheButtonWhenThereAreYellowRows()
    {
        $this->makeItem('Zqx Add All Yellow');

        $response = $this->actingAs($this->owner())->get($this->listUrl('raw'));

        $response->assertStatus(200);
        $response->assertSee('Add all to batch');
    }

    public function testRawScreenHidesTheButtonWhenEveryRowIsGrey()
    {
        $this->makeItem('Zqx Add All Grey');
        $this->skippedEarlier('Zqx Add All Grey', WeeklyBatchItem::STATUS_EXCLUDED);

        $response = $this->actingAs($this->owner())->get($this->listUrl('raw'));

        $response->assertStatus(200);
        $response->assertDontSee('Add all to batch');
    }
}
