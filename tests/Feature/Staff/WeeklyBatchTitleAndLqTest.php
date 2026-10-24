<?php

namespace Tests\Feature\Staff;

use App\Domain\WeeklyBatch\ParseService;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Models\GamesCompany;
use App\Models\User;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchExclusion;
use App\Models\WeeklyBatchItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Weekly update: titles edited by hand (#199) and LQ publishers created automatically (#198).
 */
class WeeklyBatchTitleAndLqTest extends TestCase
{
    use DatabaseTransactions;

    private WeeklyBatch $batch;

    public function setUp(): void
    {
        parent::setUp();
        // batch_date is unique; a far-future date can't clash with a real batch
        $this->batch = WeeklyBatch::create([
            'batch_date' => '2099-'.str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT).'-'.str_pad((string) random_int(1, 28), 2, '0', STR_PAD_LEFT),
            'status' => WeeklyBatch::STATUS_SETUP,
        ]);
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Weekly Batch Test',
            'email' => 'xx.weekly.batch.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeItem(array $data = []): WeeklyBatchItem
    {
        $item = new WeeklyBatchItem();
        $item->fill($data + [
            'batch_id' => $this->batch->id,
            'console' => 'switch-2',
            'list_type' => 'upcoming',
            'page_number' => 1,
            'sort_order' => 0,
            'title' => 'Zqx Weekly Test',
            'title_raw' => 'ZQX WEEKLY TEST',
            'item_status' => WeeklyBatchItem::STATUS_PENDING,
        ]);
        $item->save();
        return $item;
    }

    private function listUrl(string $name, array $extra = []): string
    {
        return route('staff.games.weekly-updates.list.'.$name, [
            'batchId' => $this->batch->id, 'console' => 'switch-2', 'listType' => 'upcoming',
        ] + $extra);
    }

    // ---- #199 ----

    public function testSaveTitle()
    {
        $item = $this->makeItem(['title' => 'Steins;gate Re:boot', 'title_raw' => 'STEINS;GATE RE:BOOT']);

        $response = $this->actingAs($this->owner())->postJson(
            $this->listUrl('item.save-title', ['itemId' => $item->id]), ['title' => 'STEINS;GATE RE:BOOT']
        );

        $response->assertOk()->assertJson(['title' => 'STEINS;GATE RE:BOOT', 'title_auto' => 'Steins;gate Re:boot']);
        $item->refresh();
        $this->assertSame('STEINS;GATE RE:BOOT', $item->title);
        $this->assertSame('Steins;gate Re:boot', $item->title_auto);
    }

    public function testRawAndConfirmScreensShowTheTitleEditor()
    {
        $this->makeItem(['title' => 'Zqx Raw Row', 'item_status' => WeeklyBatchItem::STATUS_FETCH_PENDING]);
        $ready = $this->makeItem(['title' => 'Zqx Ready Row', 'item_status' => WeeklyBatchItem::STATUS_READY]);
        $ready->setManualTitle('ZQX Ready Row Edited');
        $ready->save();

        $raw = $this->actingAs($this->owner())->get($this->listUrl('raw'));
        $raw->assertOk();
        $raw->assertSee('js-title-edit', false);
        $raw->assertSee('Upcoming: '.$this->batch->batch_date->toDateString().' onwards');

        $confirm = $this->actingAs($this->owner())->get($this->listUrl('confirm'));
        $confirm->assertOk();
        $confirm->assertSee('ZQX Ready Row Edited');
        $confirm->assertSee('Edited - auto:', false);
    }

    public function testReparseKeepsAManualTitle()
    {
        $item = $this->makeItem(['title' => 'Old Auto Title', 'title_raw' => 'ZQX REPARSE TEST']);
        $item->setManualTitle('zqx reparse EDITED');
        $item->save();

        app(ParseService::class)->reparseItem($item);

        $item->refresh();
        $this->assertSame('zqx reparse EDITED', $item->title);
        $this->assertNotSame('Old Auto Title', $item->title_auto);
        $this->assertNotNull($item->title_auto);
    }

    public function testPageReparseKeepsAManualTitle()
    {
        $snapshot = $this->makeItem(['title' => 'Zqx Snapshot', 'title_raw' => 'ZQX SNAPSHOT']);
        $snapshot->setManualTitle('ZQX Snapshot');
        $snapshot->save();
        $fresh = $this->makeItem(['title' => 'Zqx Snapshot', 'title_raw' => 'ZQX SNAPSHOT']);

        app(WeeklyBatchItemRepository::class)->restoreSnapshot($fresh, $snapshot);

        $fresh->refresh();
        $this->assertSame('ZQX Snapshot', $fresh->title);
        $this->assertSame('Zqx Snapshot', $fresh->title_auto);
    }

    public function testExclusionUsesTheAutoTitle()
    {
        $item = $this->makeItem(['title' => 'Zqx Exclusion Auto', 'title_raw' => 'ZQX EXCLUSION AUTO']);
        $item->setManualTitle('ZQX Exclusion Edited');
        $item->save();

        $this->actingAs($this->owner())->post(
            $this->listUrl('item.action', ['itemId' => $item->id]), ['action' => 'excluded']
        );

        $this->assertTrue(WeeklyBatchExclusion::where('title', 'Zqx Exclusion Auto')->where('console', 'switch-2')->exists());
        $this->assertFalse(WeeklyBatchExclusion::where('title', 'ZQX Exclusion Edited')->exists());
    }

    public function testTitleEditsPageListsEditedItems()
    {
        $item = $this->makeItem(['title' => 'Zqx Listed Auto', 'title_raw' => 'ZQX LISTED AUTO']);
        $item->setManualTitle('ZQX Listed Edited');
        $item->save();
        $this->makeItem(['title' => 'Zqx Not Edited']);

        $response = $this->actingAs($this->owner())->get(route('staff.games.weekly-updates.title-edits'));

        $response->assertOk();
        $response->assertSee('ZQX Listed Edited');
        $response->assertSee('Zqx Listed Auto');
        $response->assertDontSee('Zqx Not Edited');
    }

    // ---- #198 ----

    public function testMarkingLqCreatesAMissingPublisherAsLq()
    {
        $item = $this->makeItem([
            'item_status' => WeeklyBatchItem::STATUS_LQ_REVIEW,
            'publisher_normalised' => 'Zqx Shovelware Studio '.uniqid(),
        ]);

        $this->actingAs($this->owner())->post($this->listUrl('fetch.lq-decisions'), [
            'decisions' => [$item->id => 'low_quality'],
        ]);

        $this->assertSame(WeeklyBatchItem::STATUS_LOW_QUALITY, $item->fresh()->item_status);
        $company = GamesCompany::where('name', $item->publisher_normalised)->first();
        $this->assertNotNull($company);
        $this->assertSame(1, (int) $company->is_low_quality);
    }

    public function testMarkingLqLeavesAnExistingPublisherAlone()
    {
        $name = 'Zqx Decent Publisher '.uniqid();
        $existing = new GamesCompany();
        $existing->name = $name;
        $existing->link_title = 'zqx-decent-publisher-'.uniqid();
        $existing->is_low_quality = 0;
        $existing->save();
        $item = $this->makeItem(['publisher_normalised' => strtoupper($name)]);

        $this->actingAs($this->owner())->post(
            $this->listUrl('item.action', ['itemId' => $item->id]), ['action' => 'low_quality']
        );

        $this->assertSame(0, (int) $existing->fresh()->is_low_quality);
        $this->assertSame(1, GamesCompany::whereRaw('LOWER(name) = ?', [strtolower($name)])->count());
    }

    public function testKeepingAnItemCreatesNoPublisher()
    {
        $name = 'Zqx Kept Publisher '.uniqid();
        $item = $this->makeItem([
            'item_status' => WeeklyBatchItem::STATUS_LQ_REVIEW,
            'publisher_normalised' => $name,
        ]);

        $this->actingAs($this->owner())->post($this->listUrl('fetch.lq-decisions'), [
            'decisions' => [$item->id => 'keep'],
        ]);

        $this->assertNull(GamesCompany::where('name', $name)->first());
    }
}
