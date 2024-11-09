<?php

namespace Tests\Page\Staff;

use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;

class RoleNameTest extends TestCase
{
    /**
     * @var User
     */
    private $userOwner;

    /**
     * @var User
     */
    private $userNotStaff;

    /**
     * @var User
     */
    private $userGamesManager;

    /**
     * @var User
     */
    private $userReviewsManager;

    /**
     * @var User
     */
    private $userCategoryManager;

    /**
     * @var User
     */
    private $userPartnershipsManager;

    /**
     * @var User
     */
    private $userNewsManager;

    /**
     * @var User
     */
    private $userDataSourceManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->userNotStaff = new User(
            ['display_name' => 'Jaegerbomb', 'email' => 'xx.role.name.test.jaegerbomb@switchscores.com']
        );
        $this->userOwner = new User(
            ['display_name' => 'Bananaman', 'email' => 'xx.role.name.test.bananaman@switchscores.com', 'is_owner' => '1']
        );

        $gamesManager = new User([
            'display_name' => 'Games Manager',
            'email' => 'xx.role.name.test.games.manager@switchscores.com',
            'is_staff' => '1'
        ]);
        $reviewsManager = new User([
            'display_name' => 'Reviews Manager',
            'email' => 'xx.role.name.test.reviews.manager@switchscores.com',
            'is_staff' => '1'
        ]);
        $categoryManager = new User([
            'display_name' => 'Category Manager',
            'email' => 'xx.role.name.test.category.manager@switchscores.com',
            'is_staff' => '1'
        ]);
        $partnershipsManager = new User([
            'display_name' => 'Partnerships Manager',
            'email' => 'xx.role.name.test.partnerships.manager@switchscores.com',
            'is_staff' => '1'
        ]);
        $newsManager = new User([
            'display_name' => 'News Manager',
            'email' => 'xx.role.name.test.news.manager@switchscores.com',
            'is_staff' => '1'
        ]);
        $dsManager = new User([
            'display_name' => 'Data Source Manager',
            'email' => 'xx.role.name.test.data.source.manager@switchscores.com',
            'is_staff' => '1'
        ]);

        $gamesManager->addRole(UserRole::ROLE_GAMES_MANAGER);
        $reviewsManager->addRole(UserRole::ROLE_REVIEWS_MANAGER);
        $categoryManager->addRole(UserRole::ROLE_CATEGORY_MANAGER);
        $partnershipsManager->addRole(UserRole::ROLE_PARTNERSHIPS_MANAGER);
        $newsManager->addRole(UserRole::ROLE_NEWS_MANAGER);
        $dsManager->addRole(UserRole::ROLE_DATA_SOURCE_MANAGER);

        $this->userGamesManager = $gamesManager;
        $this->userReviewsManager = $reviewsManager;
        $this->userCategoryManager = $categoryManager;
        $this->userPartnershipsManager = $partnershipsManager;
        $this->userNewsManager = $newsManager;
        $this->userDataSourceManager = $dsManager;
    }

    public function tearDown(): void
    {
        User::where('email', 'xx.role.name.test.jaegerbomb@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.bananaman@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.games.manager@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.reviews.manager@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.category.manager@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.partnerships.manager@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.news.manager@switchscores.com')->delete();
        User::where('email', 'xx.role.name.test.data.source.manager@switchscores.com')->delete();
        parent::tearDown();
        unset($this->userOwner);
        unset($this->userNotStaff);
        unset($this->userGamesManager);
        unset($this->userReviewsManager);
        unset($this->userCategoryManager);
        unset($this->userPartnershipsManager);
        unset($this->userNewsManager);
        unset($this->userDataSourceManager);
    }

    public function testGamesDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(200);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(403);

        $this->be($this->userDataSourceManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(403);
    }

    public function testReviewsDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(200);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(403);

        $this->be($this->userDataSourceManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(403);
    }

    public function testCategorisationDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(200);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);

        $this->be($this->userDataSourceManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);
    }

    public function testGamesCompaniesDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(403);

        $this->be($this->userDataSourceManager);
        $response = $this->get('/staff/games-companies/dashboard');
        $response->assertStatus(403);
    }

    public function testDataSourceDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(403);

        $this->be($this->userDataSourceManager);
        $response = $this->get('/staff/data-sources/dashboard');
        $response->assertStatus(200);
    }

    /* NOT YET BUILT
    public function testNewsDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(403);

        $this->be($this->userEshopManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/news/dashboard');
        $response->assertStatus(200);
    }
    */
}
