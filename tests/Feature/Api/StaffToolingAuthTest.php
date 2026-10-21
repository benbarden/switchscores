<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Tests\TestCase;

/**
 * The staff-tooling API routes are behind auth.staff (#152): anonymous requests and
 * logged-in members get 401, and the public V1 game route stays open.
 */
class StaffToolingAuthTest extends TestCase
{
    private const STAFF_ROUTES = [
        '/api/game/get-by-exact-title-match',
        '/api/game/find-by-title',
        '/api/partner/games-company/search',
        '/api/review/site',
        '/api/url/link-text',
        '/api/url/news-url',
    ];

    public function testAnonymousRequestsAreRefused()
    {
        foreach (self::STAFF_ROUTES as $url) {
            $this->json('GET', $url, ['title' => 'Test'])->assertStatus(401);
        }
    }

    public function testMembersAreRefused()
    {
        $this->actingAs(new User(['display_name' => 'Api Member Test', 'is_staff' => 0, 'is_owner' => 0]));

        foreach (self::STAFF_ROUTES as $url) {
            $this->json('GET', $url, ['title' => 'Test'])->assertStatus(401);
        }
    }

    public function testPublicV1GameRouteStaysOpen()
    {
        $this->json('GET', '/api/game/1')->assertStatus(200);
    }
}
