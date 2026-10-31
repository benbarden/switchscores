<?php

namespace Tests\Feature\Members;

use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Models\Game;
use App\Models\QuickReview;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The dashboard's Recent member reviews: random, recent, capped per member, and without dates.
 */
class RecentReviewsPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function member(): User
    {
        $user = new User([
            'display_name' => 'Recent Reviews Test',
            'email' => 'xx.recent.reviews.'.uniqid().'@switchscores.com',
        ]);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function review(User $user, $createdAt, int $status = QuickReview::STATUS_ACTIVE): QuickReview
    {
        $review = new QuickReview([
            'user_id' => $user->id,
            'game_id' => Game::query()->value('id'),
            'review_score' => 8,
            'review_body' => 'Test review',
            'item_status' => $status,
        ]);
        $review->created_at = $createdAt;
        $review->save();

        return $review;
    }

    private function onlyTheseReviews(): void
    {
        QuickReview::query()->update(['item_status' => QuickReview::STATUS_REJECTED]);
    }

    public function testCapsEachMemberAtTwo()
    {
        $this->onlyTheseReviews();
        $busy = $this->member();
        for ($i = 0; $i < 6; $i++) {
            $this->review($busy, now()->subDays($i));
        }
        $other = $this->member();
        $this->review($other, now()->subDays(1));

        $picked = app(QuickReviewRepository::class)->getRandomRecentActive();

        $this->assertCount(3, $picked);
        $this->assertCount(2, $picked->where('user_id', $busy->id));
    }

    public function testStopsAtTheLimit()
    {
        $this->onlyTheseReviews();
        for ($i = 0; $i < 7; $i++) {
            $this->review($this->member(), now()->subDays($i));
        }

        $this->assertCount(5, app(QuickReviewRepository::class)->getRandomRecentActive());
    }

    public function testLeavesOutOldAndInactiveReviews()
    {
        $this->onlyTheseReviews();
        $member = $this->member();
        $this->review($member, now()->subMonths(7));
        $this->review($member, now()->subDays(1), QuickReview::STATUS_PENDING);

        $this->assertCount(0, app(QuickReviewRepository::class)->getRandomRecentActive());
    }

    public function testTheDashboardHidesTheDate()
    {
        $this->onlyTheseReviews();
        $member = $this->member();
        $createdAt = now()->subDays(3);
        $this->review($member, $createdAt);

        $this->actingAs($member)->get(route('members.index'))
            ->assertStatus(200)
            ->assertSee('Test review')
            ->assertDontSee($createdAt->format('jS M Y'));
    }
}
