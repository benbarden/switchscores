<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * The login page: what a logged-out visitor sees, for both returning members and new ones.
 */
class LoginPageTest extends TestCase
{
    public function test_it_shows_the_login_form_and_the_ways_in(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('name="email"', false);
        $response->assertSee('name="password"', false);
        $response->assertSee(route('password.request'), false);
        $response->assertSee(route('auth.login.twitter'), false);
    }

    public function test_it_gives_reasons_to_join_and_a_register_link(): void
    {
        $response = $this->get('/login');

        $response->assertSeeInOrder([
            'Track your collection',
            'Keep a wishlist',
            'Write quick reviews',
            'Find your next game',
        ]);
        $response->assertSee(route('register'), false);
    }

    public function test_the_login_form_comes_before_the_reasons_to_join_for_phones(): void
    {
        $response = $this->get('/login');

        $response->assertSeeInOrder(['name="password"', 'Track your collection'], false);
    }
}
