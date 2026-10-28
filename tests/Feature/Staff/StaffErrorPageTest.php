<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Staff errors under /staff use the staff layout; everyone else, and every public error, gets
 * the public error page as before.
 */
class StaffErrorPageTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(bool $owner = true): User
    {
        return new User([
            'display_name' => 'Staff Error Page Test',
            'email' => 'xx.staff.error.page.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => $owner ? 1 : 0,
        ]);
    }

    public function testAnUnknownStaffUrlIsAStaffNotFoundPage()
    {
        $this->actingAs($this->staff())->get('/staff/no-such-page/at-all')
            ->assertStatus(404)
            ->assertSee('Page not found')
            ->assertSee('View public site')
            ->assertSee('/staff/no-such-page/at-all');
    }

    public function testAMissingRecordIsAStaffNotFoundPage()
    {
        $this->actingAs($this->staff())->get(route('staff.games.detail', ['gameId' => 999999999]))
            ->assertStatus(404)
            ->assertSee('View public site');
    }

    public function testAMissingRoleIsAStaffForbiddenPage()
    {
        $this->actingAs($this->staff(owner: false))->get(route('staff.games.dashboard'))
            ->assertStatus(403)
            ->assertSee('Forbidden')
            ->assertSee("Your staff roles don&#039;t cover this page.", false);
    }

    public function testNonStaffStillGetTheUsualResponse()
    {
        $this->get('/staff/no-such-page')->assertStatus(401)->assertDontSee('View public site');

        $member = new User(['display_name' => 'Member', 'email' => 'xx.member.'.uniqid().'@switchscores.com']);
        $this->actingAs($member)->get('/staff/no-such-page')->assertStatus(401)->assertDontSee('View public site');
    }

    public function testAPublicNotFoundPageIsUnchanged()
    {
        $this->actingAs($this->staff())->get('/no-such-public-page')
            ->assertStatus(404)
            ->assertDontSee('View public site');
    }
}
