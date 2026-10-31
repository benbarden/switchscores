<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Newest signup in the staff home's Registration box. Owners only, as it shows an email address.
 */
class NewestSignupTest extends TestCase
{
    use DatabaseTransactions;

    private function staffUser(bool $owner): User
    {
        $user = new User([
            'display_name' => 'Newest Signup Staff',
            'email' => 'xx.newest.signup.staff.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => $owner ? 1 : 0,
        ]);
        $user->addRole(UserRole::ROLE_GAMES_MANAGER);
        $user->save();

        return $user;
    }

    private function member(array $fields = []): User
    {
        $user = new User([
            'display_name' => 'Zqx Newest Member',
            'email' => 'xx.newest.signup.member.'.uniqid().'@switchscores.com',
        ]);
        $user->created_at = now()->addDay();
        $user->forceFill($fields);
        $user->save();

        return $user->fresh();
    }

    public function testOwnerSeesTheNewestSignup()
    {
        $owner = $this->staffUser(true);
        $member = $this->member();

        $response = $this->actingAs($owner)->get('/staff');

        $response->assertStatus(200);
        $response->assertSee('Newest signup');
        $response->assertSee($member->display_name);
        $response->assertSee($member->email);
        $response->assertSee(route('owner.user.view', ['userId' => $member->id]), false);
        $response->assertSee('Joined '.$member->created_at->format('Y-m-d'));
        $response->assertSee('Unverified');
    }

    public function testVerifiedMemberHasNoUnverifiedBadge()
    {
        $owner = $this->staffUser(true);
        $this->member(['email_verified_at' => now()]);

        $response = $this->actingAs($owner)->get('/staff');

        $response->assertSee('Newest signup');
        $response->assertDontSee('bg-warning text-dark">Unverified', false);
    }

    public function testOtherStaffDontSeeIt()
    {
        $staff = $this->staffUser(false);
        $member = $this->member();

        $response = $this->actingAs($staff)->get('/staff');

        $response->assertStatus(200);
        $response->assertDontSee('Newest signup');
        $response->assertDontSee($member->email);
    }
}
