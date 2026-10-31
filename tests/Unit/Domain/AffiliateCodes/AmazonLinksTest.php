<?php

namespace Tests\Unit\Domain\AffiliateCodes;

use App\Domain\AffiliateCodes\Amazon;
use App\Models\Game;
use Tests\TestCase;

/**
 * Which Amazon link a game gets in each store: a product link if we have one, otherwise a search,
 * and nothing where the status says a search wouldn't help.
 */
class AmazonLinksTest extends TestCase
{
    private function game(array $attributes): Game
    {
        $game = new Game();
        $game->forceFill(array_merge([
            'title' => 'Test Game',
            'amazon_uk_status' => 'unchecked',
            'amazon_us_status' => 'unchecked',
        ], $attributes));

        return $game;
    }

    public function testLinkedGamesGetProductLinks()
    {
        $links = (new Amazon())->buildLinksForGame($this->game([
            'amazon_uk_status' => 'linked',
            'amazon_uk_asin' => 'B0TESTUK01',
            'amazon_us_status' => 'linked',
            'amazon_us_asin' => 'B0TESTUS01',
        ]));

        $this->assertSame('https://www.amazon.co.uk/dp/B0TESTUK01/?tag=switchscore07-21', $links->urlUk);
        $this->assertSame('product', $links->ukType);
        $this->assertSame('https://www.amazon.com/dp/B0TESTUS01/?tag=switchscores-20', $links->urlUs);
        $this->assertSame('product', $links->usType);
    }

    public function testUncheckedGamesGetASearchInEachStore()
    {
        $links = (new Amazon())->buildLinksForGame($this->game([]));

        $this->assertStringStartsWith('https://www.amazon.co.uk/s?k=', $links->urlUk);
        $this->assertStringEndsWith('&tag=switchscore07-21', $links->urlUk);
        $this->assertSame('search', $links->ukType);
        $this->assertStringStartsWith('https://www.amazon.com/s?k=', $links->urlUs);
        $this->assertStringEndsWith('&tag=switchscores-20', $links->urlUs);
        $this->assertSame('search', $links->usType);
    }

    public function testNoProductAndIgnoredGetNoLink()
    {
        foreach (['no_product', 'ignored'] as $status) {
            $links = (new Amazon())->buildLinksForGame($this->game([
                'amazon_uk_status' => $status,
                'amazon_us_status' => $status,
            ]));

            $this->assertNull($links->urlUk);
            $this->assertNull($links->ukType);
            $this->assertNull($links->urlUs);
            $this->assertNull($links->usType);
        }
    }

    public function testEachStoreFollowsItsOwnStatus()
    {
        $links = (new Amazon())->buildLinksForGame($this->game(['amazon_us_status' => 'no_product']));

        $this->assertNull($links->urlUs);
        $this->assertSame('search', $links->ukType);
    }

    public function testSearchLinksUseTheWishlistIds()
    {
        $links = (new Amazon())->forWishlist()->buildLinksForGame($this->game([]));

        $this->assertStringEndsWith('&tag=sswishlist-21', $links->urlUk);
        $this->assertStringEndsWith('&tag=sswishlist00-20', $links->urlUs);
    }
}
