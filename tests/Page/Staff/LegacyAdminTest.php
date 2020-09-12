<?php

namespace Tests\Page\Staff;

//use Illuminate\Support\Facades\Auth;
use App\User;
use Tests\TestCase;

class LegacyAdminTest extends TestCase
{
    /**
     * @var User
     */
    private $userStandard;

    /**
     * @var User
     */
    private $userAdmin;

    public function setUp(): void
    {
        parent::setUp();

        $this->userStandard = new User(
            ['display_name' => 'Stuart', 'email' => 'stu@switchscores.com']
        );
        $this->userAdmin = new User(
            ['display_name' => 'Adam', 'email' => 'adam@switchscores.com', 'is_owner' => '1']
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->userStandard);
        unset($this->userAdmin);
    }

    // Admin pages

    public function testAdminGamesList()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/admin/games/list');
        $response->assertStatus(200);
    }

    public function testAdminReviewsLink()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/staff/reviews/link');
        $response->assertStatus(200);
    }

    public function testAdminReviewsSite()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/staff/partners/review-site');
        $response->assertStatus(200);
    }

    public function testAdminTools()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/admin/tools');
        $response->assertStatus(200);
    }

    public function testAdminUserList()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/owner/user/list');
        $response->assertStatus(200);
    }
}
