<?php

namespace Tests\Feature\Staff;

use App\Domain\StaffDashboard\DbQueries;
use App\Enums\GameStatus;
use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Games to release (#197): games past their release date and not marked released.
 */
class GamesToReleaseTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Games To Release Test',
            'email' => 'xx.games.to.release.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(string $title, string $releaseDate, int $isReleased = 0): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-games-to-release-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->eu_release_date = $releaseDate;
        $game->eu_is_released = $isReleased;
        $game->save();
        return $game;
    }

    public function testPageListsOnlyGamesPastTheirDateAndNotReleased()
    {
        $due = $this->makeGame('Zqx Release Due', Carbon::today()->subDays(4)->toDateString());
        $today = $this->makeGame('Zqx Release Today', Carbon::today()->toDateString());
        $future = $this->makeGame('Zqx Release Future', Carbon::today()->addDays(3)->toDateString());
        $released = $this->makeGame('Zqx Release Done', Carbon::today()->subDays(4)->toDateString(), 1);
        $deleted = $this->makeGame('Zqx Release Deleted', Carbon::today()->subDays(4)->toDateString());
        $deleted->game_status = GameStatus::SOFT_DELETED;
        $deleted->save();

        $response = $this->actingAs($this->owner())->get(route('staff.games.games-to-release'));

        $response->assertStatus(200);
        $response->assertSee('id="release-row-'.$due->id.'"', false);
        $response->assertSee('id="release-row-'.$today->id.'"', false);
        $response->assertDontSee('id="release-row-'.$future->id.'"', false);
        $response->assertDontSee('id="release-row-'.$released->id.'"', false);
        $response->assertDontSee('id="release-row-'.$deleted->id.'"', false);
        $response->assertSee(route('staff.games.release-hub.toggle', ['id' => $due->id]), false);
    }

    public function testTileQueriesFollowTheSameRule()
    {
        $queries = app(DbQueries::class);
        $before = $queries->countGamesToRelease();

        $this->makeGame('Zqx Release Old', '2001-01-02');
        $this->makeGame('Zqx Release Future', Carbon::today()->addDays(3)->toDateString());

        $this->assertSame($before + 1, $queries->countGamesToRelease());
        $this->assertSame('2001-01-02', $queries->oldestGameToRelease()->toDateString());
    }
}
