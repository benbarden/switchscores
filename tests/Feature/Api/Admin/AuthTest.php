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
    private $userAdmin;

    public function setUp()
    {
        parent::setUp();

        $this->userStandard = new User(
            ['display_name' => 'Stuart', 'email' => 'stu@worldofswitch.com', 'is_admin' => '0']
        );
        $this->userAdmin = new User(
            ['display_name' => 'Adam', 'email' => 'adam@worldofswitch.com', 'is_admin' => '1']
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->userStandard);
        unset($this->userAdmin);
    }

    public function testAsNormalUser()
    {
        $this->be($this->userStandard);
        $response = $this->json('GET', self::API_URL_BASE);
        $response->assertStatus(401);
    }

    public function testAsAdmin()
    {
        $this->be($this->userAdmin);
        $response = $this->json('GET', self::API_URL_BASE);
        $response->assertStatus(200);
    }
}
