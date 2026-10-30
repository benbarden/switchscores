<?php

namespace Tests\Feature\Staff;

use App\Domain\User\SignupSuspicion;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Suspicious signup flags on the staff user lists. The values here are made up: the real ones
 * are set in .env, so they stay out of the repo.
 */
class SignupSuspicionTest extends TestCase
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

    private function user(array $attributes = []): User
    {
        $user = new User(array_merge([
            'display_name' => 'Ordinary Member',
            'email' => 'ordinary.member@switchscores.com',
            'signup_meta' => ['seconds_to_submit' => 40],
        ], $attributes));
        $user->id = random_int(1000000, 2000000);
        $user->created_at = now();
        return $user;
    }

    private function reasons(User $user): array
    {
        return app(SignupSuspicion::class)->reasons($user);
    }

    public function testAnOrdinarySignupIsNotFlagged()
    {
        $this->assertSame([], $this->reasons($this->user()));
    }

    public function testEachSignal()
    {
        $this->assertSame(['Display name'], $this->reasons($this->user(['display_name' => ' zqx placeholder '])));
        $this->assertSame(['Email address'], $this->reasons($this->user(['email' => 'someone123@example.com'])));
        $this->assertSame([], $this->reasons($this->user(['email' => 'someone12@example.com'])));
        $this->assertSame(['Form submitted in 2s'], $this->reasons($this->user(['signup_meta' => ['seconds_to_submit' => 2]])));
        $this->assertSame(['Form token missing or invalid'], $this->reasons($this->user(['signup_meta' => ['seconds_to_submit' => null]])));

        $old = $this->user();
        $old->created_at = now()->subHours(25);
        $this->assertSame(['No verification email requested'], $this->reasons($old));

        $old->verification_email_sent_at = now()->subHours(20);
        $this->assertSame([], $this->reasons($old));
    }

    public function testUsersWithoutSignupMetaAreOnlyCheckedOnDisplayName()
    {
        // e.g. a Twitter login: a generated address ending in digits, never verified
        $old = $this->user(['email' => 'nickname1234@switchscores.com', 'signup_meta' => null]);
        $old->created_at = now()->subYears(3);
        $this->assertSame([], $this->reasons($old));

        $old->display_name = 'Zqx Placeholder';
        $this->assertSame(['Display name'], $this->reasons($old));
    }

    public function testChecksAreOffUntilConfigured()
    {
        config(['signup-checks' => [
            'names' => [],
            'email_trailing_digits' => 0,
            'min_seconds_to_submit' => 0,
            'verification_request_hours' => 0,
        ]]);

        $user = $this->user([
            'display_name' => 'Zqx Placeholder',
            'email' => 'someone123456@example.com',
            'signup_meta' => ['seconds_to_submit' => 1],
        ]);
        $user->created_at = now()->subDays(10);

        $this->assertSame([], $this->reasons($user));
    }

    public function testTheUnverifiedMembersPageShowsTheFlag()
    {
        $suspect = User::create([
            'display_name' => 'Zqx Placeholder',
            'email' => 'xx.signup.suspicion.'.uniqid().'.member@switchscores.com',
            'password' => 'not-used',
        ]);
        $owner = new User([
            'display_name' => 'Signup Suspicion Test',
            'email' => 'xx.signup.suspicion.owner.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);

        $this->actingAs($owner)->get(route('staff.unverified-members.index'))
            ->assertStatus(200)
            ->assertSee($suspect->email)
            ->assertSee('title="Display name">Suspicious', false);
    }
}
