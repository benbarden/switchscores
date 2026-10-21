<?php

namespace Tests\Feature\Api\Game;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use Tests\TestCase;

class TitleMatchTest extends TestCase
{
    const API_URL = '/api/game/get-by-exact-title-match';

    protected function setUp(): void
    {
        parent::setUp();
        // Staff-only since #152; anonymous access is covered by StaffToolingAuthTest
        $this->actingAs(new User(['display_name' => 'Api Test', 'is_staff' => 1]));
    }

    public function testNoParams()
    {
        $response = $this->json('GET', self::API_URL);
        $response->assertStatus(400);
    }

    public function testBasicMatchPass()
    {
        $response = $this->json('GET', self::API_URL, ['title' => 'The Flame in the Flood']);
        $response->assertStatus(200);
    }

    public function testBasicMatchFail()
    {
        $response = $this->json('GET', self::API_URL, ['title' => 'The Match in the Frying Pan']);
        $response->assertJsonFragment(['gameId' => null]);
    }
}
