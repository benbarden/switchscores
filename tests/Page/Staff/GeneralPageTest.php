<?php

namespace Tests\Page\Staff;

use App\User;
use App\UserRole;
use Tests\TestCase;

class GeneralPageTest extends TestCase
{
    /**
     * @var User
     */
    private $userOwner;

    /**
     * @var User
     */
    private $userReviewsManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->userOwner = new User(
            ['display_name' => 'Bananaman', 'email' => 'bananaman@switchscores.com', 'is_owner' => '1']
        );

        $staffUserArray = [
            'display_name' => 'Jimminy Billybob',
            'email' => 'jimminy.billybob@switchscores.com',
            'is_staff' => '1'
        ];

        $reviewsManager = new User($staffUserArray);
        $reviewsManager->addRole(UserRole::ROLE_REVIEWS_MANAGER);
        $this->userReviewsManager = $reviewsManager;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->userOwner);
        unset($this->userReviewsManager);
    }

    // Reviews pages
    public function testReviewsPages()
    {
        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/reviews/dashboard');
        $response->assertStatus(200);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/reviews/quick-reviews');
        $response->assertStatus(200);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/reviews/feed-items');
        $response->assertStatus(200);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/reviews/feed-imports');
        $response->assertStatus(200);

        $this->be($this->userReviewsManager);
        $response = $this->get('/staff/reviews/feed-imports/1/items');
        $response->assertStatus(200);
    }
}
