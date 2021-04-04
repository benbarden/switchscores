<?php

namespace Tests\Page\Staff;

use App\User;
use App\UserRole;
use Tests\TestCase;

class AsGamesManagerTest extends TestCase
{
    /**
     * @var User
     */
    private $activeUser;

    private $userEmail;

    public function setUp(): void
    {
        parent::setUp();

        $userEmail = 'staff.games.manager@switchscores.com';
        $this->userEmail = $userEmail;

        $activeUser = new User([
            'display_name' => 'Games Manager',
            'email' => $userEmail,
            'is_staff' => 1
        ]);
        $activeUser->addRole(UserRole::ROLE_GAMES_MANAGER);
        $this->activeUser = $activeUser;
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
        $this->doPageTest("/staff/games/dashboard");
        $this->doPageTest("/staff/games/search");
        $this->doPageTest("/staff/games/detail/1");
        $this->doPageTest("/staff/games/detail/full-audit/1");
        $this->doPageTest("/staff/games/add");
        $this->doPageTest("/staff/games/edit/1");
        $this->doPageTest("/staff/games/edit-nintendo-co-uk/1");
        $this->doPageTest("/staff/games/delete/1");
        $this->doPageTest("/staff/games/1/import-rule-eshop/edit");
        $this->doPageTest("/staff/games/1/import-rule-wikipedia/edit");
        $this->doPageTest("/staff/games/list/games-to-release");
        $this->doPageTest("/staff/games/list/recently-added");
        $this->doPageTest("/staff/games/list/recently-released");
        $this->doPageTest("/staff/games/list/upcoming-games");
        $this->doPageTest("/staff/games/list/no-eu-release-date");
        $this->doPageTest("/staff/games/list/no-eshop-price");
        $this->doPageTest("/staff/games/list/no-video-url");
        $this->doPageTest("/staff/games/list/no-amazon-uk-link");
        $this->doPageTest("/staff/games/list/no-nintendo-co-uk-link");
        $this->doPageTest("/staff/games/list/broken-nintendo-co-uk-link");
        $this->doPageTest("/staff/games/list/by-category/1");
        $this->doPageTest("/staff/games/list/by-series/1");
        $this->doPageTest("/staff/games/list/by-tag/31");
        $this->doPageTest("/staff/games/tools/update-game-calendar-stats");
        $this->doPageTest("/staff/games/featured-games/list");
        $this->doPageTest("/staff/games/title-hash/list/1");
        $this->doPageTest("/staff/games/title-hash/add");
        $this->doPageTest("/staff/games/title-hash/edit/1");
        $this->doPageTest("/staff/games/title-hash/delete/1");
        $this->doPageTest("/staff/games/partner/1/list");
    }
}
