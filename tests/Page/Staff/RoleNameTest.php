<?php

namespace Tests\Page\Staff;

use App\User;
use App\UserRole;
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
    private $userEshopManager;

    /**
     * @var User
     */
    private $userNewsManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->userNotStaff = new User(
            ['display_name' => 'Jaegerbomb', 'email' => 'jaegerbomb@worldofswitch.com']
        );
        $this->userOwner = new User(
            ['display_name' => 'Bananaman', 'email' => 'bananaman@worldofswitch.com', 'is_owner' => '1']
        );

        $staffUserArray = [
            'display_name' => 'Jimminy Billybob',
            'email' => 'jimminy.billybob@worldofswitch.com',
            'is_staff' => '1'
        ];

        $gamesManager = new User($staffUserArray);
        $reviewsManager = new User($staffUserArray);
        $categoryManager = new User($staffUserArray);
        $partnershipsManager = new User($staffUserArray);
        $eshopManager = new User($staffUserArray);
        $newsManager = new User($staffUserArray);

        $gamesManager->addRole(UserRole::ROLE_GAMES_MANAGER);
        $reviewsManager->addRole(UserRole::ROLE_REVIEWS_MANAGER);
        $categoryManager->addRole(UserRole::ROLE_CATEGORY_MANAGER);
        $partnershipsManager->addRole(UserRole::ROLE_PARTNERSHIPS_MANAGER);
        $eshopManager->addRole(UserRole::ROLE_ESHOP_MANAGER);
        $newsManager->addRole(UserRole::ROLE_NEWS_MANAGER);

        $this->userGamesManager = $gamesManager;
        $this->userReviewsManager = $reviewsManager;
        $this->userCategoryManager = $categoryManager;
        $this->userPartnershipsManager = $partnershipsManager;
        $this->userEshopManager = $eshopManager;
        $this->userNewsManager = $newsManager;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->userOwner);
        unset($this->userNotStaff);
        unset($this->userGamesManager);
        unset($this->userReviewsManager);
        unset($this->userCategoryManager);
        unset($this->userPartnershipsManager);
        unset($this->userEshopManager);
        unset($this->userNewsManager);
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

        $this->be($this->userEshopManager);
        $response = $this->get('/staff/games/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
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

        $this->be($this->userEshopManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
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

        $this->be($this->userEshopManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/categorisation/dashboard');
        $response->assertStatus(403);
    }

    public function testPartnersDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(200);

        $this->be($this->userEshopManager);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(403);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/partners/dashboard');
        $response->assertStatus(403);
    }

    public function testEshopDashboard()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(403);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(403);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(403);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(403);

        $this->be($this->userEshopManager);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(200);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff/eshop/dashboard');
        $response->assertStatus(403);
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
