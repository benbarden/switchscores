<?php

namespace Tests\Page\Staff;

use App\User;
use App\UserRole;
use Tests\TestCase;

class RoleBasicTest extends TestCase
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
            ['display_name' => 'Jaegerbomb', 'email' => 'jaegerbomb@switchscores.com']
        );
        $this->userOwner = new User(
            ['display_name' => 'Bananaman', 'email' => 'bananaman@switchscores.com', 'is_owner' => '1']
        );

        $staffUserArray = [
            'display_name' => 'Jimminy Billybob',
            'email' => 'jimminy.billybob@switchscores.com',
            'is_staff' => '1'
        ];

        $gamesManager = new User($staffUserArray);
        $reviewsManager = new User($staffUserArray);
        $categoryManager = new User($staffUserArray);
        $partnershipsManager = new User($staffUserArray);
        $newsManager = new User($staffUserArray);
        $dsManager = new User($staffUserArray);

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

    // General tests
    public function testLoggedOut()
    {
        $response = $this->get('/staff');
        $response->assertStatus(401);
    }

    public function testAsNormalUser()
    {
        $this->be($this->userNotStaff);
        $response = $this->get('/staff');
        $response->assertStatus(401);
    }

    public function testAsOwner()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff');
        $response->assertStatus(200);
    }

    // Staff index
    public function testStaffIndex()
    {
        $this->be($this->userOwner);
        $response = $this->get('/staff');
        $response->assertStatus(200);

        $this->be($this->userNotStaff);
        $response = $this->get('/staff');
        $response->assertStatus(401);

        $this->be($this->userGamesManager);
        $response = $this->get('/staff');
        $response->assertStatus(200);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff');
        $response->assertStatus(200);

        $this->be($this->userCategoryManager);
        $response = $this->get('/staff');
        $response->assertStatus(200);

        $this->be($this->userPartnershipsManager);
        $response = $this->get('/staff');
        $response->assertStatus(200);

        $this->be($this->userNewsManager);
        $response = $this->get('/staff');
        $response->assertStatus(200);

        $this->be($this->userDataSourceManager);
        $response = $this->get('/staff');
        $response->assertStatus(200);
    }
}
