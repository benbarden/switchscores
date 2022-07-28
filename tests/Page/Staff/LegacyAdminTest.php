<?php

namespace Tests\Page\Staff;

//use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Tests\TestCase;

class LegacyAdminTest extends TestCase
{
    /**
     * @var \App\Models\User
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
            ['display_name' => 'Stuart', 'email' => 'xx.tester.stu@switchscores.com']
        );
        $this->userAdmin = new User(
            ['display_name' => 'Adam', 'email' => 'xx.tester.adam@switchscores.com', 'is_owner' => '1']
        );
    }

    public function tearDown(): void
    {
        User::where('email', 'xx.tester.stu@switchscores.com')->delete();
        User::where('email', 'xx.tester.adam@switchscores.com')->delete();
        parent::tearDown();
        unset($this->userStandard);
        unset($this->userAdmin);
    }

    // Admin pages

    public function testAdminReviewsLink()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/staff/reviews/link');
        $response->assertStatus(200);
    }

    public function testAdminReviewsSite()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/staff/reviews/review-sites');
        $response->assertStatus(200);
    }

    public function testAdminUserList()
    {
        $this->be($this->userAdmin);
        $response = $this->get('/owner/user/list');
        $response->assertStatus(200);
    }
}
