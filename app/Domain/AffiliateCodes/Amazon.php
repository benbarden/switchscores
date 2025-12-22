<?php

namespace App\Domain\AffiliateCodes;

use App\Models\Game;

class Amazon
{
    const AMAZON_UK_ID = 'switchscore07-21';

    const AMAZON_US_ID = 'switchscores-20';

    protected string $ukId;
    protected string $usId;

    public function __construct()
    {
        $this->ukId = self::AMAZON_UK_ID;
        $this->usId = self::AMAZON_US_ID;
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
        $ukLink = $this->buildUkLink($game);
        [$usLink, $usType] = $this->buildUsLink($game);

        return new AmazonLinkResult(
            urlUk: $ukLink,
            urlUs: $usLink,
            usType: $usType
        );
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
     * UK product link builder.
     */
    protected function buildUkLink(Game $game): ?string
    {
        if ($game->amazon_uk_asin) {
            return $this->buildUkLinkFromAsin($game->amazon_uk_asin);
        }

        if ($game->amazon_uk_link) {
            return $this->appendAffiliateTag($game->amazon_uk_link, $this->ukId);
        }

        return null;
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

        return [
            $this->buildSearchLink($game->title),
            'search',
        ];
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
     * Create a fallback Amazon US search link.
     * Automatically URL-encodes the search term.
     */
    protected function buildSearchLink(string $title): string
    {
        // Remove bad punctuation that breaks Amazon search
        $cleanTitle = trim($title);
        $cleanTitle = preg_replace('/[^A-Za-z0-9 ]/', '', $cleanTitle);

        $query = urlencode("nintendo switch games " . $cleanTitle);

        return "https://www.amazon.com/s?k={$query}&tag={$this->usId}";
    }
}