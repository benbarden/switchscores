<?php

namespace Tests\Feature\Auth;

use App\Domain\User\SignupMeta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Registration: the signup details saved to users.signup_meta, and the Turnstile check.
 */
class RegisterTest extends TestCase
{
    use DatabaseTransactions;

    private function signupData(array $overrides = []): array
    {
        $pass = 'test-pass-'.uniqid();
        return array_merge([
            'signup_name' => 'Register Test',
            'signup_email' => 'xx.register.test.'.uniqid().'@switchscores.com',
            'signup_pass' => $pass,
            'signup_pass_confirmation' => $pass,
            SignupMeta::FORM_TOKEN_FIELD => SignupMeta::formToken(),
        ], $overrides);
    }

    private function userFor(array $data): ?User
    {
        return User::where('email', strtolower($data['signup_email']))->first();
    }

    public function testTheFormCarriesATokenAndNoOldCampaignFields()
    {
        $this->get(route('register'))
            ->assertStatus(200)
            ->assertSee('name="form_token"', false)
            ->assertDontSee('signup_alpha');
    }

    public function testSignupSavesTheRequestDetails()
    {
        $this->travelTo(now());
        $data = $this->signupData();
        $this->travel(12)->seconds();

        $this->withHeaders([
            'User-Agent' => 'Register Test Agent',
            'Accept-Language' => 'en-GB',
            'Referer' => 'https://example.com/from',
            'CF-IPCountry' => 'GB',
        ])->post(route('register'), $data)->assertRedirect();

        $meta = $this->userFor($data)->signup_meta;
        $this->assertSame('Register Test Agent', $meta['user_agent']);
        $this->assertSame('en-GB', $meta['accept_language']);
        $this->assertSame('https://example.com/from', $meta['referrer']);
        $this->assertSame('GB', $meta['country']);
        $this->assertSame(12, $meta['seconds_to_submit']);
        $this->assertSame('off', $meta['turnstile']);
        $this->assertNotEmpty($meta['ip']);
    }

    public function testAMissingOrTamperedTokenGivesNoTime()
    {
        $missing = $this->signupData([SignupMeta::FORM_TOKEN_FIELD => null]);
        $this->post(route('register'), $missing)->assertRedirect();
        $this->assertNull($this->userFor($missing)->signup_meta['seconds_to_submit']);

        auth()->logout();

        $tampered = $this->signupData([SignupMeta::FORM_TOKEN_FIELD => 'not-a-real-token']);
        $this->post(route('register'), $tampered)->assertRedirect();
        $this->assertNull($this->userFor($tampered)->signup_meta['seconds_to_submit']);
    }

    public function testTurnstileRejectsAMissingTokenWhenSwitchedOn()
    {
        config(['services.turnstile.secret' => 'test-secret']);
        $data = $this->signupData();

        $this->post(route('register'), $data)->assertSessionHasErrors('turnstile');
        $this->assertNull($this->userFor($data));
    }

    public function testTurnstilePassIsRecorded()
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);
        $data = $this->signupData(['cf-turnstile-response' => 'test-token']);

        $this->post(route('register'), $data)->assertSessionHasNoErrors();
        $this->assertSame('passed', $this->userFor($data)->signup_meta['turnstile']);
    }
}
