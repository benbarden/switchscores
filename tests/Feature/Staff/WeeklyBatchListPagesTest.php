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
 * The weekly update list screens: the Raw page's tables, and the URLs and Packshots steps, which
 * have one table and one Save button across every page.
 */
class WeeklyBatchListPagesTest extends TestCase
{
    use DatabaseTransactions;

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2097-07-'.str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Weekly List Pages Test',
            'email' => 'xx.weekly.list.pages.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeItem(string $title, array $data = []): WeeklyBatchItem
    {
        return WeeklyBatchItem::create($data + [
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => 'new',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => $title,
            'title_raw' => $title,
            'item_status' => WeeklyBatchItem::STATUS_PENDING,
        ]);
    }

    private function makeGame(string $title): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-list-pages-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();
        return $game;
    }

    private function listUrl(string $name, string $listType = 'new'): string
    {
        return route('staff.games.weekly-updates.list.'.$name, [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => $listType,
        ]);
    }

    public function testOneUrlSaveCoversEveryPage()
    {
        $first = $this->makeItem('Zqx List Page One');
        $second = $this->makeItem('Zqx List Page Two', ['page_number' => 2]);

        $response = $this->actingAs($this->owner())->post($this->listUrl('urls.save'), ['urls' => [
            $first->id => 'https://www.nintendo.com/en-gb/one.html',
            $second->id => 'https://www.nintendo.com/en-gb/two.html',
        ]]);

        $response->assertSessionHas('success', 'URLs saved.');
        $this->assertSame(WeeklyBatchItem::STATUS_FETCH_PENDING, $first->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_FETCH_PENDING, $second->fresh()->item_status);
    }

    public function testSavingAnUnchangedUrlLeavesAFetchedItemAlone()
    {
        $url = 'https://www.nintendo.com/en-gb/ready.html';
        $ready = $this->makeItem('Zqx List Ready', ['nintendo_url' => $url, 'item_status' => WeeklyBatchItem::STATUS_READY]);

        $this->actingAs($this->owner())->post($this->listUrl('urls.save'), ['urls' => [$ready->id => $url]]);

        $this->assertSame(WeeklyBatchItem::STATUS_READY, $ready->fresh()->item_status);
    }

    public function testOnePackshotSaveCoversEveryPageAndLeavesUnchangedItemsAlone()
    {
        $first = $this->makeItem('Zqx List Packshot One', ['item_status' => WeeklyBatchItem::STATUS_PACKSHOT_PENDING]);
        $second = $this->makeItem('Zqx List Packshot Two', ['page_number' => 2, 'item_status' => WeeklyBatchItem::STATUS_PACKSHOT_PENDING]);
        $ready = $this->makeItem('Zqx List Packshot Ready', [
            'page_number' => 2,
            'packshot_url' => 'https://img.example/ready.jpg',
            'item_status' => WeeklyBatchItem::STATUS_READY,
        ]);

        $response = $this->actingAs($this->owner())->post($this->listUrl('packshots.save'), ['packshots' => [
            $first->id => 'https://img.example/one.jpg',
            $second->id => 'https://img.example/two.jpg',
            $ready->id => 'https://img.example/ready.jpg',
        ]]);

        $response->assertSessionHas('success', '2 packshots saved.');
        $this->assertSame(WeeklyBatchItem::STATUS_CATEGORY_PENDING, $first->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_CATEGORY_PENDING, $second->fresh()->item_status);
        $this->assertSame(WeeklyBatchItem::STATUS_READY, $ready->fresh()->item_status);
    }

    public function testRawPageMergesGamesFromEarlierWeeksIntoAlreadyInDb()
    {
        $this->makeItem('Zqx List In DB', ['item_status' => WeeklyBatchItem::STATUS_ALREADY_IN_DB]);
        $this->makeItem('Zqx List Earlier In DB', [
            'item_status' => WeeklyBatchItem::STATUS_OUT_OF_RANGE,
            'game_id' => $this->makeGame('Zqx List Earlier In DB')->id,
        ]);

        $response = $this->actingAs($this->owner())->get($this->listUrl('raw'));

        $response->assertStatus(200);
        $response->assertSee('Already in DB (2)');
        $response->assertDontSee('out of range', false);
        $response->assertDontSee('No exact date');
    }

    public function testRawPageShowsUpcomingGamesWithNoExactDate()
    {
        $this->makeItem('Zqx List Vague Date', [
            'list_type' => 'upcoming',
            'item_status' => WeeklyBatchItem::STATUS_OUT_OF_RANGE,
            'release_date_raw' => 'May 2027',
        ]);

        $response = $this->actingAs($this->owner())->get($this->listUrl('raw', 'upcoming'));

        $response->assertStatus(200);
        $response->assertSee('No exact date (1)');
        $response->assertSee('No exact date: May 2027');
    }
}
