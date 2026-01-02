<?php

namespace Tests\Page\User;

use App\Models\User;
use Tests\TestCase;

class StandardUserTest extends TestCase
{
    /**
     * @var User
     */
    private $activeUser;

    private $userEmail;

    public function setUp(): void
    {
        parent::setUp();

        $userEmail = 'mr.spiderman.of.the.west@switchscores.com';
        $this->userEmail = $userEmail;

        $this->activeUser = new User([
            'display_name' => 'Spiderman',
            'email' => $userEmail
        ]);
    }

    public function tearDown(): void
    {
        User::where('email', $this->userEmail)->delete();
        unset($this->activeUser);
        parent::tearDown();
    }

    public function doPageTest($url)
    {
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    public function testUserPages()
    {
        $this->be($this->activeUser);
        $this->doPageTest("/members");
        $this->doPageTest("/members/search-modular/add-quick-review");
        $this->doPageTest("/members/collection/index");
        $this->doPageTest("/members/collection/list/not-started");
        $this->doPageTest("/members/collection/add?gameId=6657");
        //$this->doPageTest("/members/collection/edit/1");
        $this->doPageTest("/members/collection/category-breakdown");
        $this->doPageTest("/members/collection/top-rated-by-category/1");
        $this->doPageTest("/members/quick-reviews/add/1");
        $this->doPageTest("/members/quick-reviews");
        $this->doPageTest("/members/featured-games/add/1");
        $this->doPageTest("/members/campaigns/1");
    }
}
