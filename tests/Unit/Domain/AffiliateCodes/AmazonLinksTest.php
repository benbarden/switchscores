<?php

namespace Tests\Unit\Domain\AffiliateCodes;

use App\Domain\AffiliateCodes\Amazon;
use App\Models\Game;
use Tests\TestCase;

/**
 * Which Amazon link a game gets in each store: a product link if we have one. Otherwise US gets
 * a search, unless the status says a search wouldn't help, and UK gets nothing, as Amazon UK
 * only pays for direct product page links.
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

    public function testUncheckedGamesGetAUsSearchAndNoUkLink()
    {
        $links = (new Amazon())->buildLinksForGame($this->game([]));

        $this->assertNull($links->urlUk);
        $this->assertNull($links->ukType);
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
        $links = (new Amazon())->buildLinksForGame($this->game([
            'amazon_uk_status' => 'linked',
            'amazon_uk_asin' => 'B0TESTUK01',
            'amazon_us_status' => 'no_product',
        ]));

        $this->assertNull($links->urlUs);
        $this->assertSame('product', $links->ukType);
    }

    public function testSearchKeepsAccentedLettersAsPlainOnes()
    {
        $links = (new Amazon())->buildLinksForGame($this->game(['title' => 'Pokémon Pokopia']));

        $this->assertStringContainsString('Pokemon+Pokopia', $links->urlUs);
    }

    public function testLinksUseTheWishlistIds()
    {
        $links = (new Amazon())->forWishlist()->buildLinksForGame($this->game([
            'amazon_uk_asin' => 'B0TESTUK01',
        ]));

        $this->assertSame('https://www.amazon.co.uk/dp/B0TESTUK01/?tag=sswishlist-21', $links->urlUk);
        $this->assertStringEndsWith('&tag=sswishlist00-20', $links->urlUs);
    }
}
