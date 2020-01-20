<?php

namespace Tests\Feature\Api\Admin;

use Illuminate\Support\Facades\Auth;
use App\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    const API_URL_BASE = '/api/admin/auth-test';

    /**
     * @var User
     */
    private $userStandard;

    /**
     * @var User
     */
    private $userOwner;

    public function setUp(): void
    {
        parent::setUp();

        $this->userStandard = new User(
            ['display_name' => 'Stuart', 'email' => 'stu@switchscores.com', 'is_owner' => '0']
        );
        $this->userOwner = new User(
            ['display_name' => 'Aaron', 'email' => 'aaron@switchscores.com', 'is_owner' => '1']
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->userStandard);
        unset($this->userOwner);
    }

    public function testAsNormalUser()
    {
        $this->be($this->userStandard);
        $response = $this->json('GET', self::API_URL_BASE);
        $response->assertStatus(401);
    }

    public function testAsOwner()
    {
        $this->be($this->userOwner);
        $response = $this->json('GET', self::API_URL_BASE);
        $response->assertStatus(200);
    }
}
