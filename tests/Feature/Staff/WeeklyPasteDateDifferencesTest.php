<?php

namespace Tests\Feature\Staff;

use App\Domain\StaffDashboard\AttentionTiles;
use App\Domain\StaffDashboard\TileState;
use App\Domain\WeeklyBatch\DateDifferences;
use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use App\Models\UserRole;
use App\Models\WeeklyBatch;
use App\Models\WeeklyBatchItem;
use App\Models\WeeklyPasteDateIgnore;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Release date differences from the weekly paste (#194). Upcoming only, from the most recent
 * batch with Upcoming pasted for each console. Batches are dated 2099 so they are always the
 * most recent in the localdev data the tests run against.
 */
class WeeklyPasteDateDifferencesTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Weekly Paste Dates Test',
            'email' => 'xx.weekly.paste.dates.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(?string $euReleaseDate, int $isReleased = 0): Game
    {
        $game = new Game();
        $game->title = 'Zqx Paste Dates '.uniqid();
        $game->link_title = 'test-paste-dates-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->eu_release_date = $euReleaseDate;
        $game->release_year = $euReleaseDate ? substr($euReleaseDate, 0, 4) : null;
        $game->eu_is_released = $isReleased;
        $game->save();
        return $game;
    }

    private function makeBatch(string $batchDate): WeeklyBatch
    {
        return WeeklyBatch::create(['batch_date' => $batchDate, 'status' => WeeklyBatch::STATUS_SETUP]);
    }

    private function makeItem(WeeklyBatch $batch, Game $game, ?string $releaseDate, string $listType = 'upcoming'): WeeklyBatchItem
    {
        return WeeklyBatchItem::create([
            'batch_id' => $batch->id,
            'console' => 'switch-2',
            'list_type' => $listType,
            'page_number' => 1,
            'sort_order' => 0,
            'title' => $game->title,
            'title_raw' => $game->title,
            'release_date' => $releaseDate,
            'item_status' => WeeklyBatchItem::STATUS_ALREADY_IN_DB,
            'game_id' => $game->id,
        ]);
    }

    private function differenceFor(Game $game): ?object
    {
        return (new DateDifferences())->get($game->id)[0] ?? null;
    }

    public function testTileCountsDifferencesAndAgesFromTheBatch()
    {
        $counts = ['reviewDrafts' => 0, 'quickReviews' => 0, 'featuredGames' => 0, 'contact' => 0, 'signups' => 0];
        $this->makeItem($this->makeBatch('2099-01-01'), $this->makeGame('2099-03-01'), '2099-03-15');

        $tiles = app(AttentionTiles::class)->build($this->owner(), $counts, Carbon::parse('2099-01-10'));
        $tile = collect($tiles)->firstWhere('key', 'paste-differences');

        $this->assertSame((new DateDifferences())->count(), $tile['count']);
        $this->assertGreaterThan(0, $tile['count']);
        $this->assertSame(TileState::AMBER, $tile['state']);
    }

    public function testWeeklyUpdatesPageLinksToBothPasteChecks()
    {
        $this->makeItem($this->makeBatch('2099-01-01'), $this->makeGame('2099-03-01'), '2099-03-15');

        $response = $this->actingAs($this->owner())->get(route('staff.games.weekly-updates.index'));

        $response->assertStatus(200);
        $response->assertSee(route('staff.data-sources.differences.weekly-paste.release-date'), false);
        $response->assertSee(route('staff.data-sources.differences.weekly-paste.missing'), false);
        $response->assertSee('<span class="badge bg-warning text-dark">'.(new DateDifferences())->count().'</span>', false);
    }

    public function testWeeklyUpdatesPageHidesThePasteChecksWithoutTheRole()
    {
        $user = new User([
            'display_name' => 'Weekly Paste Dates Test',
            'email' => 'xx.weekly.paste.dates.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
        ]);
        $user->addRole(UserRole::ROLE_GAMES_MANAGER);

        $response = $this->actingAs($user)->get(route('staff.games.weekly-updates.index'));

        $response->assertStatus(200);
        $response->assertDontSee(route('staff.data-sources.differences.weekly-paste.release-date'), false);
    }

    public function testFindsAMovedUpcomingDate()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');

        $row = $this->differenceFor($game);

        $this->assertNotNull($row);
        $this->assertSame('2099-03-01', $row->eu_release_date);
        $this->assertSame('2099-03-15', $row->paste_release_date);
        $this->assertEquals(14, $row->days_moved);
        $this->assertNull($row->status_warning);
    }

    public function testFindsAGameWithNoDate()
    {
        $game = $this->makeGame(null);
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');

        $this->assertNotNull($this->differenceFor($game));
    }

    public function testSameDateNullDateAndNewListAreLeftOut()
    {
        $batch = $this->makeBatch('2099-01-01');
        $same = $this->makeGame('2099-03-01');
        $this->makeItem($batch, $same, '2099-03-01');
        $yearOnly = $this->makeGame('2099-03-01');
        $this->makeItem($batch, $yearOnly, null);
        $newList = $this->makeGame('2099-03-01');
        $this->makeItem($batch, $newList, '2099-03-15', 'new');

        $this->assertNull($this->differenceFor($same));
        $this->assertNull($this->differenceFor($yearOnly));
        $this->assertNull($this->differenceFor($newList));
    }

    public function testOnlyTheMostRecentBatchCounts()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');
        $other = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-08'), $other, '2099-03-01');

        $this->assertNull($this->differenceFor($game));
    }

    public function testIgnoredDateIsLeftOutUntilTheDateChangesAgain()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');
        WeeklyPasteDateIgnore::create(['game_id' => $game->id, 'release_date' => '2099-03-15']);

        $this->assertNull($this->differenceFor($game));

        $this->makeItem($this->makeBatch('2099-01-08'), $game, '2099-04-01');

        $this->assertNotNull($this->differenceFor($game));
    }

    public function testWarnsWhenAReleasedGameGetsAFutureDate()
    {
        $game = $this->makeGame('2021-06-28', 1);
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');

        $this->assertNotNull($this->differenceFor($game)->status_warning);
    }

    public function testApplySetsDateAndYear()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2100-01-10');

        $response = $this->actingAs($this->owner())->post(
            route('staff.data-sources.differences.weekly-paste.release-date.apply'),
            ['game_id' => $game->id, 'release_date' => '2100-01-10']
        );

        $response->assertRedirect(route('staff.data-sources.differences.weekly-paste.release-date'));
        $response->assertSessionHas('pasteDatesSuccess');
        $game->refresh();
        $this->assertSame('2100-01-10', $game->eu_release_date);
        $this->assertEquals(2100, $game->release_year);
        $this->assertSame(0, (int) $game->eu_is_released);
    }

    public function testApplyRefusesADateThePasteNoLongerShows()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');

        $response = $this->actingAs($this->owner())->post(
            route('staff.data-sources.differences.weekly-paste.release-date.apply'),
            ['game_id' => $game->id, 'release_date' => '2099-04-01']
        );

        $response->assertSessionHas('pasteDatesError');
        $this->assertSame('2099-03-01', $game->fresh()->eu_release_date);
    }

    public function testIgnoreAndRemove()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');

        $this->actingAs($this->owner())->post(
            route('staff.data-sources.differences.weekly-paste.release-date.ignore'),
            ['game_id' => $game->id, 'release_date' => '2099-03-15']
        )->assertSessionHas('pasteDatesSuccess');

        $this->assertNull($this->differenceFor($game));
        $this->assertSame('2099-03-01', $game->fresh()->eu_release_date);

        $ignore = WeeklyPasteDateIgnore::where('game_id', $game->id)->first();
        $this->actingAs($this->owner())->get(route('staff.data-sources.differences.weekly-paste.release-date.ignored'))
            ->assertStatus(200)
            ->assertSee($game->title);

        $this->actingAs($this->owner())->post(
            route('staff.data-sources.differences.weekly-paste.release-date.ignored.remove'),
            ['id' => $ignore->id]
        );

        $this->assertNotNull($this->differenceFor($game));
    }

    public function testPageShowsTheDifference()
    {
        $game = $this->makeGame('2099-03-01');
        $this->makeItem($this->makeBatch('2099-01-01'), $game, '2099-03-15');

        $response = $this->actingAs($this->owner())->get(route('staff.data-sources.differences.weekly-paste.release-date'));

        $response->assertStatus(200);
        $response->assertSee($game->title);
        $response->assertSee('2099-03-15');
    }
}
