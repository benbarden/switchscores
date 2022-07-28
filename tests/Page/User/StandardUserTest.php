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
        $this->doPageTest("/user");
        $this->doPageTest("/user/search-modular/add-quick-review");
        $this->doPageTest("/user/collection/index");
        $this->doPageTest("/user/collection/list/not-started");
        $this->doPageTest("/user/collection/add?gameId=6657");
        //$this->doPageTest("/user/collection/edit/1");
        $this->doPageTest("/user/collection/category-breakdown");
        $this->doPageTest("/user/collection/top-rated-by-category/1");
        $this->doPageTest("/user/quick-reviews/add/1");
        $this->doPageTest("/user/quick-reviews");
        $this->doPageTest("/user/featured-games/add/1");
        $this->doPageTest("/user/campaigns/1");
        //$this->doPageTest("/user/games-list/upcoming");
        $this->doPageTest("/user/settings");
    }
}
