<?php

namespace Tests\Feature\Members;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\ActivityLog;
use App\Models\Game;
use App\Models\User;
use App\Models\UserWishlist;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Wishlist adds and removes are logged in the activity log, and the owner's
 * activity log page can be filtered by event type.
 */
class ActivityLogWishlistTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function member(): User
    {
        $user = new User([
            'display_name' => 'Activity Log Test '.uniqid(),
            'email' => 'xx.activity.log.'.uniqid().'@switchscores.com',
        ]);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function owner(): User
    {
        return new User([
            'display_name' => 'Activity Log Owner',
            'email' => 'xx.activity.log.owner.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function logRows(User $member)
    {
        return ActivityLog::where('user_id', $member->id)->orderBy('id')->get();
    }

    public function testAddingToTheWishlistIsLogged()
    {
        $member = $this->member();
        $game = Game::query()->firstOrFail();

        $this->actingAs($member)->post(route('members.wishlist.add', ['gameId' => $game->id]))
            ->assertStatus(200);

        $rows = $this->logRows($member);
        $this->assertCount(1, $rows);
        $this->assertSame(ActivityLog::EVENT_TYPE_USER_WISHLIST_ADDED, $rows[0]->event_type);
        $this->assertSame(ActivityLog::EVENT_MODEL_USER_WISHLIST, $rows[0]->event_model);
        $this->assertEquals($game->id, $rows[0]->event_model_id);
    }

    public function testRemovingFromTheWishlistIsLogged()
    {
        $member = $this->member();
        $game = Game::query()->firstOrFail();
        UserWishlist::create(['user_id' => $member->id, 'game_id' => $game->id]);

        $this->actingAs($member)->post(route('members.wishlist.remove', ['gameId' => $game->id]))
            ->assertStatus(200);

        $rows = $this->logRows($member);
        $this->assertCount(1, $rows);
        $this->assertSame(ActivityLog::EVENT_TYPE_USER_WISHLIST_REMOVED, $rows[0]->event_type);
        $this->assertEquals($game->id, $rows[0]->event_model_id);
    }

    public function testRemovingAGameNotOnTheWishlistLogsNothing()
    {
        $member = $this->member();
        $game = Game::query()->firstOrFail();

        $this->actingAs($member)->post(route('members.wishlist.remove', ['gameId' => $game->id]))
            ->assertStatus(200);

        $this->assertCount(0, $this->logRows($member));
    }

    public function testAddingAWishlistGameToTheCollectionLogsBoth()
    {
        $member = $this->member();
        $game = Game::query()->firstOrFail();
        UserWishlist::create(['user_id' => $member->id, 'game_id' => $game->id]);

        $this->actingAs($member)->post(route('members.collection.add'), ['game_id' => $game->id])
            ->assertRedirect();

        $types = $this->logRows($member)->pluck('event_type')->all();
        $this->assertSame([
            ActivityLog::EVENT_TYPE_USER_WISHLIST_REMOVED,
            ActivityLog::EVENT_TYPE_USER_GAME_COLLECTION_ADDED,
        ], $types);
    }

    public function testFilterShowsOnlyTheChosenType()
    {
        $wishlistMember = $this->member();
        $collectionMember = $this->member();
        $game = Game::query()->firstOrFail();

        ActivityLog::create([
            'event_type' => ActivityLog::EVENT_TYPE_USER_WISHLIST_ADDED,
            'user_id' => $wishlistMember->id,
            'event_model' => ActivityLog::EVENT_MODEL_USER_WISHLIST,
            'event_model_id' => $game->id,
        ]);
        ActivityLog::create([
            'event_type' => ActivityLog::EVENT_TYPE_USER_GAME_COLLECTION_ADDED,
            'user_id' => $collectionMember->id,
            'event_model' => 'App\Domain\UserGamesCollection',
            'event_model_id' => $game->id,
        ]);

        $this->actingAs($this->owner())
            ->get(route('owner.activityLog', ['type' => ActivityLog::EVENT_TYPE_USER_WISHLIST_ADDED]))
            ->assertStatus(200)
            ->assertSee($wishlistMember->display_name)
            ->assertDontSee($collectionMember->display_name)
            ->assertSee('<option value="UserWishlistAdded" selected>', false);
    }

    public function testAnUnknownTypeShowsAllRows()
    {
        $wishlistMember = $this->member();
        $collectionMember = $this->member();
        $game = Game::query()->firstOrFail();

        ActivityLog::create([
            'event_type' => ActivityLog::EVENT_TYPE_USER_WISHLIST_REMOVED,
            'user_id' => $wishlistMember->id,
            'event_model' => ActivityLog::EVENT_MODEL_USER_WISHLIST,
            'event_model_id' => $game->id,
        ]);
        ActivityLog::create([
            'event_type' => ActivityLog::EVENT_TYPE_USER_GAME_COLLECTION_REMOVED,
            'user_id' => $collectionMember->id,
            'event_model' => 'App\Domain\UserGamesCollection',
            'event_model_id' => $game->id,
        ]);

        $this->actingAs($this->owner())
            ->get(route('owner.activityLog', ['type' => 'NotAType']))
            ->assertStatus(200)
            ->assertSee($wishlistMember->display_name)
            ->assertSee($collectionMember->display_name);
    }
}
