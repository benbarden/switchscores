<?php

namespace Tests\Feature\Members;

use App\Domain\AffiliateCodes\Amazon;
use App\Models\Game;
use App\Models\User;
use App\Models\UserWishlist;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Buy buttons, rank and sale price on the member wishlist, tagged with the wishlist's own
 * tracking IDs so Amazon's reports split out what it earns.
 */
class WishlistBuyButtonsTest extends TestCase
{
    use DatabaseTransactions;

    private function member(): User
    {
        $user = new User([
            'display_name' => 'Wishlist Buttons Test',
            'email' => 'xx.wishlist.buttons.'.uniqid().'@switchscores.com',
        ]);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    private function wishlistedGame(User $member, array $attributes): Game
    {
        $game = Game::query()->firstOrFail();
        $game->forceFill(array_merge([
            'amazon_uk_asin' => 'B0TESTUK01',
            'amazon_us_asin' => 'B0TESTUS01',
            'amazon_uk_link' => null,
            'amazon_us_link' => null,
            'price_eshop' => null,
            'price_eshop_discounted' => null,
            'price_eshop_discount_pc' => null,
        ], $attributes))->save();

        UserWishlist::create(['user_id' => $member->id, 'game_id' => $game->id]);

        return $game;
    }

    public function testButtonsUseTheWishlistTrackingIds()
    {
        $member = $this->member();
        $this->wishlistedGame($member, []);

        $this->actingAs($member)->get(route('members.wishlist.index'))
            ->assertStatus(200)
            ->assertSee('amazon.co.uk/dp/B0TESTUK01/?tag=sswishlist-21', false)
            ->assertSee('amazon.com/dp/B0TESTUS01/?tag=sswishlist00-20', false)
            ->assertSee('rel="nofollow sponsored"', false);
    }

    public function testShowsRankAndSalePrice()
    {
        $member = $this->member();
        $this->wishlistedGame($member, [
            'rating_avg' => 8.5,
            'game_rank' => 123,
            'price_eshop' => 39.99,
            'price_eshop_discounted' => 19.99,
            'price_eshop_discount_pc' => 50,
        ]);

        $this->actingAs($member)->get(route('members.wishlist.index'))
            ->assertSee('Rank #123')
            ->assertSee('19.99')
            ->assertSee('50% off');
    }

    public function testNoSaleLineWithoutADiscount()
    {
        $member = $this->member();
        $this->wishlistedGame($member, ['price_eshop' => 39.99]);

        $this->actingAs($member)->get(route('members.wishlist.index'))
            ->assertDontSee('% off');
    }

    public function testOtherPagesKeepTheStandardIds()
    {
        $amazon = app(Amazon::class);
        $amazon->forWishlist();

        $this->assertSame(Amazon::AMAZON_UK_ID, $amazon->getUKId());
        $this->assertSame(Amazon::AMAZON_US_ID, $amazon->getUSId());
        $this->assertSame('sswishlist-21', $amazon->forWishlist()->getUKId());
        $this->assertSame('sswishlist00-20', $amazon->forWishlist()->getUSId());
    }
}
