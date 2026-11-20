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
 * The batch page's cards and the batch list use the Raw page's counts: games from earlier weeks
 * that are in the DB count as already in DB, Upcoming shows No exact date, and the list splits
 * LQ, Bundle and Excluded.
 */
class WeeklyBatchSummaryCountsTest extends TestCase
{
    use DatabaseTransactions;

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch, and sorts first
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2099-12-'.str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Weekly Summary Counts Test',
            'email' => 'xx.weekly.summary.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function item(string $listType, string $status, ?int $gameId = null): void
    {
        WeeklyBatchItem::create([
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => $listType,
            'page_number' => 1,
            'sort_order' => 0,
            'title' => 'Zqx Summary '.uniqid(),
            'title_raw' => 'Zqx Summary',
            'item_status' => $status,
            'game_id' => $gameId,
        ]);
    }

    private function gameId(): int
    {
        $game = new Game();
        $game->title = 'Zqx Summary Game';
        $game->link_title = 'test-summary-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();
        return $game->id;
    }

    public function testBatchPageCountsGamesFromEarlierWeeksAsAlreadyInDb()
    {
        $this->item('new', WeeklyBatchItem::STATUS_ALREADY_IN_DB, $this->gameId());
        $this->item('new', WeeklyBatchItem::STATUS_OUT_OF_RANGE, $this->gameId());
        $this->item('new', WeeklyBatchItem::STATUS_EXCLUDED);
        $this->item('upcoming', WeeklyBatchItem::STATUS_OUT_OF_RANGE);

        $response = $this->actingAs($this->owner())->get(route('staff.games.weekly-updates.show', ['batchId' => $this->batch->id]));

        $response->assertStatus(200);
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertStringContainsString('<td>Already in DB</td><td>2</td>', $html);
        $this->assertStringContainsString('<td>Excluded</td><td>1</td>', $html);
        $this->assertStringContainsString('<td>No exact date</td><td>1</td>', $html);
    }

    public function testBatchListSplitsLqBundleAndExcluded()
    {
        $this->item('new', WeeklyBatchItem::STATUS_LOW_QUALITY);
        $this->item('new', WeeklyBatchItem::STATUS_LOW_QUALITY);
        $this->item('upcoming', WeeklyBatchItem::STATUS_BUNDLE);
        $this->item('new', WeeklyBatchItem::STATUS_EXCLUDED);
        $this->item('new', WeeklyBatchItem::STATUS_EXCLUDED);
        $this->item('new', WeeklyBatchItem::STATUS_EXCLUDED);

        $response = $this->actingAs($this->owner())->get(route('staff.games.weekly-updates.index'));

        $response->assertStatus(200);
        $html = preg_replace('/\s+/', ' ', $response->getContent());
        $this->assertMatchesRegularExpression(
            '#'.$this->batch->batch_date->format('d M Y').'.*?<span class="count text-muted">2</span>.*?<span class="count text-muted">1</span>.*?<span class="count text-muted">3</span>#',
            $html
        );
    }
}
