<?php

namespace App\Domain\AffiliateCodes;

use App\Enums\AmazonAffiliateStatus;
use App\Models\Console;
use App\Models\Game;
use Illuminate\Support\Str;

class Amazon
{
    const AMAZON_UK_ID = 'switchscore07-21';

    const AMAZON_US_ID = 'switchscores-20';

    // Separate IDs, so Amazon's reports split out what the wishlist earns
    const AMAZON_UK_WISHLIST_ID = 'sswishlist-21';

    const AMAZON_US_WISHLIST_ID = 'sswishlist00-20';

    // The buyer's guide has its own IDs for the same reason
    const AMAZON_UK_BUYERS_GUIDE_ID = 'ssbuyersguide-21';

    const AMAZON_US_BUYERS_GUIDE_ID = 'ssbuyersguide-20';

    protected string $ukId;
    protected string $usId;

    public function __construct()
    {
        $this->ukId = self::AMAZON_UK_ID;
        $this->usId = self::AMAZON_US_ID;
    }

    /**
     * The same link builder, tagged with the wishlist's tracking IDs.
     */
    public function forWishlist(): static
    {
        $wishlist = clone $this;
        $wishlist->ukId = self::AMAZON_UK_WISHLIST_ID;
        $wishlist->usId = self::AMAZON_US_WISHLIST_ID;

        return $wishlist;
    }

    /**
     * The same link builder, tagged with the buyer's guide's tracking IDs.
     */
    public function forBuyersGuide(): static
    {
        $guide = clone $this;
        $guide->ukId = self::AMAZON_UK_BUYERS_GUIDE_ID;
        $guide->usId = self::AMAZON_US_BUYERS_GUIDE_ID;

        return $guide;
    }

    /**
     * A product link for an ASIN that isn't a game, such as a console or accessory.
     */
    public function productLink(string $region, string $asin): string
    {
        return $region === 'uk' ? $this->buildUkLinkFromAsin($asin) : $this->buildUsLinkFromAsin($asin);
    }

    public function getUKId()
    {
        return $this->ukId;
    }

    public function getUSId()
    {
        return $this->usId;
    }

    /**
     * Build a set of affiliate links (UK + US) for a specific game.
     */
    public function buildLinksForGame(Game $game): AmazonLinkResult
    {
        [$ukLink, $ukType] = $this->buildUkLink($game);
        [$usLink, $usType] = $this->buildUsLink($game);

        return new AmazonLinkResult(
            urlUk: $ukLink,
            urlUs: $usLink,
            usType: $usType,
            ukType: $ukType
        );
    }

    /**
     * Amazon search for staff finding a product. Untagged, as these aren't visitor clicks.
     */
    public function staffSearchUrl(Game $game, string $region): string
    {
        $domain = $region === 'uk' ? 'www.amazon.co.uk' : 'www.amazon.com';
        $console = $game->console_id == Console::ID_SWITCH_2 ? 'nintendo switch 2 games' : 'nintendo switch games';

        return 'https://'.$domain.'/s?'.http_build_query(['k' => $game->title.' '.$console]);
    }

    protected function buildUkLinkFromAsin(string $asin): string
    {
        return sprintf(
            'https://www.amazon.co.uk/dp/%s/?tag=%s',
            rawurlencode($asin),
            $this->ukId
        );
    }

    protected function buildUsLinkFromAsin(string $asin): string
    {
        return sprintf(
            'https://www.amazon.com/dp/%s/?tag=%s',
            rawurlencode($asin),
            $this->usId
        );
    }

    /**
     * UK product link builder. No search fallback: Amazon UK only pays affiliates for
     * direct product page links, so a search click earns nothing.
     */
    protected function buildUkLink(Game $game): array
    {
        if ($game->amazon_uk_asin) {
            return [
                $this->buildUkLinkFromAsin($game->amazon_uk_asin),
                'product',
            ];
        }

        if ($game->amazon_uk_link) {
            return [
                $this->appendAffiliateTag($game->amazon_uk_link, $this->ukId),
                'product',
            ];
        }

        return [null, null];
    }

    /**
     * US product link builder with fallback search.
     */
    protected function buildUsLink(Game $game): array
    {
        if ($game->amazon_us_asin) {
            return [
                $this->buildUsLinkFromAsin($game->amazon_us_asin),
                'product',
            ];
        }

        if ($game->amazon_us_link) {
            return [
                $this->appendAffiliateTag($game->amazon_us_link, $this->usId),
                'product',
            ];
        }

        if (!$this->canSearch($game->amazon_us_status)) {
            return [null, null];
        }

        return [
            $this->buildSearchLink($game->title, 'www.amazon.com', $this->usId),
            'search',
        ];
    }

    /**
     * A search would mislead for a game checked and found not to be on sale there, and ignored
     * games are low quality or de-listed, so neither gets a button.
     */
    protected function canSearch(?string $status): bool
    {
        return !in_array($status, [
            AmazonAffiliateStatus::NO_PRODUCT->value,
            AmazonAffiliateStatus::IGNORED->value,
        ], true);
    }

    /**
     * Append ?tag= or &tag= depending on existing query params.
     */
    protected function appendAffiliateTag(string $url, string $affiliateId): string
    {
        if (str_contains($url, '?')) {
            return $url . '&tag=' . $affiliateId;
        }

        return $url . '?tag=' . $affiliateId;
    }

    /**
     * Create a fallback Amazon search link.
     * Automatically URL-encodes the search term.
     */
    protected function buildSearchLink(string $title, string $domain, string $affiliateId): string
    {
        // Plain letters first, so Pokémon becomes Pokemon rather than losing the é
        $cleanTitle = trim(Str::ascii($title));

        // Remove bad punctuation that breaks Amazon search
        $cleanTitle = preg_replace('/[^A-Za-z0-9 ]/', '', $cleanTitle);

        $query = urlencode("nintendo switch games " . $cleanTitle);

        return "https://{$domain}/s?k={$query}&tag={$affiliateId}";
    }
}