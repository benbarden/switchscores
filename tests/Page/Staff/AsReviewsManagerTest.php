<?php

namespace Tests\Page\Staff;

use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;

class AsReviewsManagerTest extends TestCase
{
    /**
     * @var User
     */
    private $userReviewsManager;

    public function setUp(): void
    {
        parent::setUp();

        $reviewsManager = new User([
            'display_name' => 'Roger the Review Manager',
            'email' => 'roger.review.manager@switchscores.com',
            'is_staff' => 1
        ]);
        $reviewsManager->addRole(UserRole::ROLE_REVIEWS_MANAGER);

        $this->userReviewsManager = $reviewsManager;
    }

    public function tearDown(): void
    {
        User::where('email', $this->userReviewsManager->email)->delete();
        unset($this->userReviewsManager);
        parent::tearDown();
    }

    public function doPageTest($url)
    {
        $response = $this->get($url);
        $response->assertStatus(200);
    }

    // Reviews pages
    public function testDashboard()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/dashboard');
    }

    public function testReviewSites()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/review-sites');
        $this->doPageTest('/staff/reviews/review-sites/add');
        $this->doPageTest('/staff/reviews/review-sites/edit/1');
    }

    public function testFeedLinks()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/feed-links');
        $this->doPageTest('/staff/reviews/feed-links/add');
        $this->doPageTest('/staff/reviews/feed-links/edit/1');
    }

    public function testReviewLinks()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/link');
        $this->doPageTest('/staff/reviews/link/add');
        $this->doPageTest('/staff/reviews/link/edit/1');
        $this->doPageTest('/staff/reviews/link/delete');
    }

    public function testQuickReviews()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/quick-reviews');
        $this->doPageTest('/staff/reviews/quick-reviews/edit/1');
        $this->doPageTest('/staff/reviews/quick-reviews/delete/1');
    }

    public function testReviewDrafts()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/review-drafts/pending');
        $this->doPageTest('/staff/reviews/review-drafts/by-process-status/Not in database');
    }

    public function testCampaigns()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/campaigns');
        $this->doPageTest('/staff/reviews/campaigns/add');
        $this->doPageTest('/staff/reviews/campaigns/edit/1');
        $this->doPageTest('/staff/reviews/campaigns/edit-games/1');
        $this->doPageTest('/staff/reviews/campaigns');
    }

    public function testUnranked()
    {
        $this->be($this->userReviewsManager);
        $this->doPageTest('/staff/reviews/unranked/review-count');
        $this->doPageTest('/staff/reviews/unranked/review-count/0/list');
        $this->doPageTest('/staff/reviews/unranked/review-count/1/list');
        $this->doPageTest('/staff/reviews/unranked/review-count/2/list');
        $this->doPageTest('/staff/reviews/unranked/release-year');
        $this->doPageTest('/staff/reviews/unranked/release-year/2017/list');
        $this->doPageTest('/staff/reviews/unranked/release-year/2018/list');
        $this->doPageTest('/staff/reviews/unranked/release-year/2019/list');
        $this->doPageTest('/staff/reviews/unranked/release-year/2020/list');
        $this->doPageTest('/staff/reviews/unranked/release-year/2021/list');
        $this->doPageTest('/staff/reviews/unranked/release-year/2022/list');
    }
}
