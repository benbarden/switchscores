<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The owner's user view page: the Flags row, and the badges after the display name.
 */
class OwnerUserViewTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['signup-checks' => [
            'names' => ['Zqx Placeholder'],
            'email_trailing_digits' => 3,
            'min_seconds_to_submit' => 5,
            'verification_request_hours' => 24,
        ]]);
    }

    private function viewUser(array $attributes = [], bool $verified = true)
    {
        $user = new User(array_merge([
            'display_name' => 'Ordinary Member',
            'email' => 'xx.owner.user.view.'.uniqid().'.member@switchscores.com',
        ], $attributes));
        if ($verified) {
            $user->email_verified_at = now();
        }
        $user->save();

        $owner = new User([
            'display_name' => 'Owner User View Test',
            'email' => 'xx.owner.user.view.owner.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);

        return $this->actingAs($owner)->get(route('owner.user.view', ['userId' => $user->id]));
    }

    public function testAVerifiedMemberHasNoFlags()
    {
        $response = $this->viewUser();

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Flags', 'None', 'Display name']);
        $response->assertDontSee('>Unverified<', false);
        $response->assertDontSee('>Suspicious<', false);
    }

    public function testUnverifiedShowsAfterTheEmailAndNotAsAFlag()
    {
        $response = $this->viewUser(['email' => 'xx.owner.user.view.unverified.member@switchscores.com'], false);

        $response->assertSeeInOrder(['Flags', 'None', 'xx.owner.user.view.unverified.member@switchscores.com', '>Unverified<'], false);
        $response->assertDontSee('>Suspicious<', false);
    }

    public function testShowsSuspiciousWithItsReasons()
    {
        $response = $this->viewUser(['display_name' => 'Zqx Placeholder']);

        $response->assertSee('>Suspicious<', false);
        $response->assertSee('<small>Display name</small>', false);
    }

    public function testShowsReviewerAfterTheDisplayName()
    {
        $this->viewUser(['partner_id' => 1])->assertSee('>Reviewer<', false);
    }

    public function testShowsGamesCompanyAfterTheDisplayName()
    {
        $this->viewUser(['games_company_id' => 1])->assertSee('>Games company<', false);
    }
}
