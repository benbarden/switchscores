<?php

namespace Tests\Page\Staff;

use App\Models\UserRole;
use App\User;
use Tests\TestCase;

class NewsPageTest extends TestCase
{
    /**
     * @var User
     */
    private $userOwner;

    /**
     * @var User
     */
    private $newsManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->userOwner = new User(
            ['display_name' => 'Bananaman', 'email' => 'xx.news.page.test.bananaman@switchscores.com', 'is_owner' => '1']
        );

        $staffUserArray = [
            'display_name' => 'Jimminy Billybob',
            'email' => 'xx.news.page.test.jimminy.billybob@switchscores.com',
            'is_staff' => '1'
        ];

        $newsManager = new User($staffUserArray);
        $newsManager->addRole(UserRole::ROLE_NEWS_MANAGER);
        $this->newsManager = $newsManager;
    }

    public function tearDown(): void
    {
        User::where('email', 'xx.news.page.test.bananaman@switchscores.com')->delete();
        User::where('email', 'xx.news.page.test.jimminy.billybob@switchscores.com')->delete();
        parent::tearDown();
        unset($this->userOwner);
        unset($this->newsManager);
    }

    public function testNewsPages()
    {
        $this->be($this->newsManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(200);

        $this->be($this->newsManager);
        $response = $this->get('/staff/news/list');
        $response->assertStatus(200);

        $this->be($this->newsManager);
        $response = $this->get('/staff/news/add');
        $response->assertStatus(200);

        $this->be($this->newsManager);
        $response = $this->get('/staff/news/edit/1');
        $response->assertStatus(200);
    }

    public function testNewsCategoryPages()
    {
        $this->be($this->newsManager);
        $response = $this->get('/staff/news/category/list');
        $response->assertStatus(200);

        $this->be($this->newsManager);
        $response = $this->get('/staff/news/category/add');
        $response->assertStatus(200);

        $this->be($this->newsManager);
        $response = $this->get('/staff/news/category/edit/1');
        $response->assertStatus(200);
    }
}
