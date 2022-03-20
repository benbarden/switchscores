<?php

namespace Tests\Page\Staff;

use App\Models\User;
use App\Models\UserRole;
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

        $ownerUserArray = [
            'display_name' => 'Bananaman',
            'email' => 'bananaman@switchscores.com',
        ];

        $owner = new User([
            'display_name' => 'Oscar the Owner',
            'email' => 'oscar.owner@switchscores.com',
            'is_owner' => 1
        ]);

        $reviewsManager = new User([
            'display_name' => 'Roger the Review Manager',
            'email' => 'roger.review.manager@switchscores.com',
            'is_staff' => 1
        ]);
        $reviewsManager->addRole(UserRole::ROLE_REVIEWS_MANAGER);

        //$reviewsManager = new User($staffUserArray);
        //$reviewsManager->addRole(UserRole::ROLE_REVIEWS_MANAGER);
        $this->userOwner = $owner;
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
