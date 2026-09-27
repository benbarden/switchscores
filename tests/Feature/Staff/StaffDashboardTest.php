<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use App\Models\UserRole;
use Tests\TestCase;

/**
 * The staff home's needs-attention tiles (#163) render, and each role only sees the tiles for
 * queues it could already see on the old staff home.
 */
class StaffDashboardTest extends TestCase
{
    // The staff middleware saves the user on each request, so every test user gets its own
    // email and is removed afterwards.
    private array $emails = [];

    public function tearDown(): void
    {
        User::whereIn('email', $this->emails)->delete();
        parent::tearDown();
    }

    private function staffUser(array $roles, bool $owner = false): User
    {
        $email = 'xx.staff.dashboard.test.'.count($this->emails).'.'.uniqid().'@switchscores.com';
        $this->emails[] = $email;
        $user = new User([
            'display_name' => 'Dashboard Test',
            'email' => $email,
            'is_staff' => 1,
            'is_owner' => $owner ? 1 : 0,
        ]);
        foreach ($roles as $role) {
            $user->addRole($role);
        }
        return $user;
    }

    public function testOwnerSeesEveryTile()
    {
        $response = $this->actingAs($this->staffUser([], true))->get('/staff');

        $response->assertStatus(200);
        $response->assertSee('Needs attention');
        foreach (['review-drafts', 'quick-reviews', 'featured-games', 'contact', 'feeds', 'jobs',
                     'integrity', 'signups'] as $key) {
            $response->assertSee('data-tile="'.$key.'"', false);
        }
        $response->assertSee('Site health');
    }

    /**
     * Nintendo.co.uk unlinked includes future-dated games, so it's never meant to reach zero: a
     * row under Nintendo links, not a tile.
     */
    public function testNintendoUnlinkedIsARowNotATile()
    {
        $response = $this->actingAs($this->staffUser([], true))->get('/staff');

        $response->assertDontSee('data-tile="nintendo-unlinked"', false);
        $response->assertSee('Nintendo.co.uk API: Unlinked items');
    }

    public function testReviewsManagerSeesReviewTilesOnly()
    {
        $response = $this->actingAs($this->staffUser([UserRole::ROLE_REVIEWS_MANAGER]))->get('/staff');

        $response->assertStatus(200);
        $response->assertSee('data-tile="review-drafts"', false);
        $response->assertSee('data-tile="quick-reviews"', false);
        $response->assertSee('data-tile="feeds"', false);
        foreach (['featured-games', 'contact', 'jobs', 'integrity', 'signups'] as $key) {
            $response->assertDontSee('data-tile="'.$key.'"', false);
        }
    }

    public function testGamesManagerSeesFeaturedGamesButNotReviewTiles()
    {
        $response = $this->actingAs($this->staffUser([UserRole::ROLE_GAMES_MANAGER]))->get('/staff');

        $response->assertStatus(200);
        $response->assertSee('data-tile="featured-games"', false);
        $response->assertDontSee('data-tile="review-drafts"', false);
        $response->assertDontSee('data-tile="jobs"', false);
    }

    /**
     * Checks the page body only. "Import games from JSON" and "Duplicate reviews" still live in the
     * staff top menu (Games / Data), which is on every staff page and was deliberately left alone.
     * "Invite code requests" is archived: linked from nowhere.
     */
    public function testRemovedLinksAreGoneFromTheStaffHome()
    {
        $response = $this->actingAs($this->staffUser([], true))->get('/staff');
        $html = $response->getContent();
        $body = substr($html, strpos($html, '<h1>'));

        $this->assertStringNotContainsString('Invite code requests', $html);
        $this->assertStringNotContainsString('Import games from JSON', $body);
        $this->assertStringNotContainsString('No category with collection', $body);
        $this->assertStringNotContainsString('Duplicate reviews', $body);
    }
}
