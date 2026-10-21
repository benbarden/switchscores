<?php

namespace Tests\Feature\Staff;

use App\Models\ReviewSite;
use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;

/**
 * Review site stats, moved under Reviews from the retired Stats section (#20).
 * A reviews manager who isn't the owner can now open both pages.
 */
class ReviewSiteStatsTest extends TestCase
{
    private function reviewsManager(): User
    {
        $user = new User([
            'display_name' => 'Review Site Stats Test',
            'email' => 'xx.review.site.stats.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 0,
        ]);
        $user->addRole(UserRole::ROLE_REVIEWS_MANAGER);
        return $user;
    }

    public function testStatsTable()
    {
        $response = $this->actingAs($this->reviewsManager())->get(route('staff.reviews.reviewSiteStats'));

        $response->assertStatus(200);
        $response->assertSee('Review share');
        $response->assertDontSee('Released games');
    }

    public function testSiteChart()
    {
        $site = ReviewSite::first();
        if (!$site) {
            $this->markTestSkipped('No review sites in this database.');
        }

        $response = $this->actingAs($this->reviewsManager())->get(route('staff.reviews.reviewSites.stats', ['siteId' => $site->id]));

        $response->assertStatus(200);
        $response->assertSee('review-link-stats', false);
    }

    public function testReviewsDashboardLinksToStats()
    {
        $response = $this->actingAs($this->reviewsManager())->get(route('staff.reviews.dashboard'));

        $response->assertSee(route('staff.reviews.reviewSiteStats'), false);
    }

    public function testOldStatsUrlsAreGone()
    {
        $owner = new User(['display_name' => 'Owner', 'is_staff' => 1, 'is_owner' => 1]);

        $this->actingAs($owner)->get('/staff/stats/dashboard')->assertStatus(404);
        $this->actingAs($owner)->get('/staff/stats/review-site')->assertStatus(404);
    }
}
