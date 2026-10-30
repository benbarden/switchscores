<?php

namespace Tests\Feature\Staff;

use App\Models\Console;
use App\Models\Game;
use App\Models\QuickReview;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Approve, Reject and Delete on the staff quick reviews list go back to the status filter they
 * were used from. Pending is status 0, so that's the case to watch.
 */
class QuickReviewFilterTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Quick Review Filter Test',
            'email' => 'xx.quick.review.filter.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeReview(): QuickReview
    {
        $game = new Game();
        $game->title = 'Zqx Quick Review Filter';
        $game->link_title = 'test-quick-review-filter-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->save();

        $member = new User([
            'display_name' => 'Quick Review Member',
            'email' => 'xx.quick.review.member.test.'.uniqid().'@switchscores.com',
        ]);
        $member->save();

        return QuickReview::create([
            'user_id' => $member->id,
            'game_id' => $game->id,
            'review_score' => 7.5,
            'review_body' => 'Zqx review body',
            'item_status' => QuickReview::STATUS_PENDING,
        ]);
    }

    private function listUrl(array $params = []): string
    {
        return route('staff.reviews.quick-reviews.list', $params);
    }

    public function testApproveKeepsThePendingFilter()
    {
        $review = $this->makeReview();

        $response = $this->actingAs($this->owner())->post(
            route('staff.reviews.quick-reviews.approve', ['reviewId' => $review->id]),
            ['filterStatus' => '0']
        );

        $response->assertRedirect($this->listUrl(['filterStatus' => '0']));
        $this->assertEquals(QuickReview::STATUS_ACTIVE, $review->fresh()->item_status);
    }

    public function testRejectKeepsThePendingFilter()
    {
        $review = $this->makeReview();

        $response = $this->actingAs($this->owner())->post(
            route('staff.reviews.quick-reviews.reject', ['reviewId' => $review->id]),
            ['filterStatus' => '0']
        );

        $response->assertRedirect($this->listUrl(['filterStatus' => '0']));
        $this->assertEquals(QuickReview::STATUS_REJECTED, $review->fresh()->item_status);
    }

    public function testDeleteKeepsThePendingFilterThroughTheConfirmPage()
    {
        $review = $this->makeReview();
        $owner = $this->owner();
        $deleteUrl = route('staff.reviews.quick-reviews.delete', ['reviewId' => $review->id]);

        $page = $this->actingAs($owner)->get($deleteUrl.'?filterStatus=0');
        $page->assertStatus(200);
        $page->assertSee('name="filterStatus" value="0"', false);

        $response = $this->actingAs($owner)->post($deleteUrl, ['filterStatus' => '0']);

        $response->assertRedirect($this->listUrl(['filterStatus' => '0']));
        $this->assertNull(QuickReview::find($review->id));
    }

    public function testNoFilterGoesBackToTheFullList()
    {
        $review = $this->makeReview();

        // The All filter posts an empty value
        $response = $this->actingAs($this->owner())->post(
            route('staff.reviews.quick-reviews.approve', ['reviewId' => $review->id]),
            ['filterStatus' => '']
        );

        $response->assertRedirect($this->listUrl());
    }

    public function testListCarriesTheFilterToItsActions()
    {
        $review = $this->makeReview();

        $response = $this->actingAs($this->owner())->get($this->listUrl(['filterStatus' => '0']));

        $response->assertStatus(200);
        $response->assertSee('name="filterStatus" value="0"', false);
        $deleteUrl = route('staff.reviews.quick-reviews.delete', ['reviewId' => $review->id, 'filterStatus' => '0']);
        $response->assertSee('href="'.$deleteUrl.'"', false);
    }
}
