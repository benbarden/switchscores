<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Games dashboard tidy-up (#189): affiliates subpage, inline search, "Go to game ID".
 */
class GamesDashboardTest extends TestCase
{
    // The request middleware saves the logged-in user (last_access_date); roll it back (#200)
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Games Dashboard Test',
            'email' => 'xx.games.dashboard.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    public function testDashboardHasSearchAndGoToGame()
    {
        $response = $this->actingAs($this->owner())->get(route('staff.games.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('name="search_keywords"', false);
        $response->assertSee(route('staff.games.goToGame'), false);
        $response->assertSee(route('staff.games.affiliates'), false);
        $response->assertDontSee('Amazon US: Unchecked');
    }

    public function testAffiliatesPage()
    {
        $response = $this->actingAs($this->owner())->get(route('staff.games.affiliates'));

        $response->assertStatus(200);
        $response->assertSee('Amazon US');
        $response->assertSee('Amazon UK');
    }

    public function testGoToGameRedirectsToDetail()
    {
        $response = $this->actingAs($this->owner())->get(route('staff.games.goToGame', ['game_id' => 1]));

        $response->assertRedirect(route('staff.games.detail', ['gameId' => 1]));
    }

    public function testGoToMissingGameReturnsToDashboard()
    {
        $response = $this->actingAs($this->owner())->get(route('staff.games.goToGame', ['game_id' => 999999999]));

        $response->assertRedirect(route('staff.games.dashboard'));
        $response->assertSessionHas('goToGameError');
    }

    public function testGoToNonNumericIdReturnsToDashboard()
    {
        $response = $this->actingAs($this->owner())->get(route('staff.games.goToGame', ['game_id' => 'abc']));

        $response->assertRedirect(route('staff.games.dashboard'));
        $response->assertSessionHas('goToGameError');
    }
}
