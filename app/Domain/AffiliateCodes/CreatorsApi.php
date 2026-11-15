<?php

namespace App\Domain\AffiliateCodes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Amazon Creators API client for Amazon US: staff product searches, and offer lookups by ASIN
 * for the buyer's guide.
 *
 * Search results are shown to staff to pick from and never stored: only the ASIN chosen is
 * saved, through the usual row save. The credential and token must never be logged or shown.
 */
class CreatorsApi
{
    const TOKEN_URL = 'https://api.amazon.com/auth/o2/token';

    const SEARCH_URL = 'https://creatorsapi.amazon/catalog/v1/searchItems';

    const GET_ITEMS_URL = 'https://creatorsapi.amazon/catalog/v1/getItems';

    // Amazon's limit on ASINs in one getItems request
    const GET_ITEMS_MAX = 10;

    const OFFER_RESOURCES = [
        'itemInfo.title',
        'images.primary.large',
        'offersV2.listings.price',
        'offersV2.listings.isBuyBoxWinner',
    ];

    const MARKETPLACE = 'www.amazon.com';

    const TOKEN_CACHE_KEY = 'amazon-creators-token';

    // Tokens last an hour; refresh a little early so one never expires mid-request
    const TOKEN_CACHE_SECONDS = 3300;

    const TIMEOUT_SECONDS = 10;

    const RESOURCES = [
        'itemInfo.title',
        'itemInfo.byLineInfo',
        'images.primary.small',
        'offersV2.listings.price',
    ];

    public function isConfigured(): bool
    {
        return !empty(config('services.amazon_creators.credential_id'))
            && !empty(config('services.amazon_creators.credential_secret'));
    }

    /**
     * Search Amazon US video games. Each result has asin, title, brand, imageUrl and price
     * (any of the last three can be null).
     *
     * @throws CreatorsApiException
     */
    public function searchItems(string $keywords, int $itemCount = 5): array
    {
        $response = $this->request(self::SEARCH_URL, [
            'keywords' => $keywords,
            'searchIndex' => 'VideoGames',
            'itemCount' => $itemCount,
            'resources' => self::RESOURCES,
            'marketplace' => self::MARKETPLACE,
            'partnerTag' => Amazon::AMAZON_US_ID,
        ]);

        $results = [];
        foreach ($response['searchResult']['items'] ?? [] as $item) {
            if (empty($item['asin'])) {
                continue;
            }
            $results[] = [
                'asin' => $item['asin'],
                'title' => $item['itemInfo']['title']['displayValue'] ?? null,
                'brand' => $item['itemInfo']['byLineInfo']['brand']['displayValue'] ?? null,
                'imageUrl' => $item['images']['primary']['small']['url'] ?? null,
                'price' => $this->price($item['offersV2']['listings'] ?? []),
            ];
        }

        return $results;
    }

    /**
     * Price, saving and image for up to 10 ASINs on Amazon US, keyed by ASIN. An ASIN Amazon
     * doesn't return is left out. Each item has:
     *   title, imageUrl, price (display text), wasPrice (display text), savingBasisType
     *   (e.g. LIST_PRICE, WAS_PRICE), savingsPercent (int) and violatesMap (bool).
     * Any of them can be null. A listing that violates MAP comes back with no price, and must
     * never have one shown.
     *
     * @param string[] $asins
     * @throws CreatorsApiException
     */
    public function getItems(array $asins): array
    {
        if (count($asins) > self::GET_ITEMS_MAX) {
            throw new \InvalidArgumentException('getItems takes at most '.self::GET_ITEMS_MAX.' ASINs.');
        }

        $response = $this->request(self::GET_ITEMS_URL, [
            'itemIds' => array_values($asins),
            'itemIdType' => 'ASIN',
            'resources' => self::OFFER_RESOURCES,
            'marketplace' => self::MARKETPLACE,
            'partnerTag' => Amazon::AMAZON_US_ID,
        ]);

        $results = [];
        foreach ($response['itemsResult']['items'] ?? [] as $item) {
            if (empty($item['asin'])) {
                continue;
            }
            $listing = $this->listing($item['offersV2']['listings'] ?? []);
            $price = $listing['price'] ?? [];
            $percent = $price['savings']['percentage'] ?? null;

            $results[$item['asin']] = [
                'title' => $item['itemInfo']['title']['displayValue'] ?? null,
                'imageUrl' => $item['images']['primary']['large']['url'] ?? null,
                'price' => $price['money']['displayAmount'] ?? null,
                'wasPrice' => $price['savingBasis']['money']['displayAmount'] ?? null,
                'savingBasisType' => $price['savingBasis']['savingBasisType'] ?? null,
                'savingsPercent' => is_numeric($percent) ? (int) $percent : null,
                'violatesMap' => !empty($listing['violatesMAP']),
            ];
        }

        return $results;
    }

    /**
     * The buy box price if there is one, otherwise the first listing's.
     */
    private function price(array $listings): ?string
    {
        return $this->listing($listings)['price']['money']['displayAmount'] ?? null;
    }

    /**
     * The buy box listing if there is one, otherwise the first.
     */
    private function listing(array $listings): array
    {
        foreach ($listings as $listing) {
            if (!empty($listing['isBuyBoxWinner'])) {
                return $listing;
            }
        }

        return $listings[0] ?? [];
    }

    private function request(string $url, array $body): array
    {
        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withToken($this->token())
                ->withHeaders(['x-marketplace' => self::MARKETPLACE])
                ->asJson()
                ->post($url, $body);
        } catch (CreatorsApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CreatorsApiException('Could not reach Amazon. Try again in a moment.');
        }

        if ($response->status() === 401) {
            // A token Amazon no longer accepts; the next request gets a new one
            Cache::forget(self::TOKEN_CACHE_KEY);
        }

        if (!$response->successful()) {
            throw new CreatorsApiException($this->failureMessage($response->status(), $response->body()));
        }

        return $response->json() ?? [];
    }

    private function failureMessage(int $status, string $body): string
    {
        if ($status === 429) {
            return 'Amazon says too many requests. Wait a second and try again.';
        }

        // Amazon's error bodies name the problem (e.g. AssociateNotEligible) and hold no credentials
        $detail = trim(mb_substr(strip_tags($body), 0, 200));

        return 'Amazon returned HTTP '.$status.($detail !== '' ? ': '.$detail : '.');
    }

    private function token(): string
    {
        if (!$this->isConfigured()) {
            throw new CreatorsApiException('The Creators API credentials are not set.');
        }

        $cached = Cache::get(self::TOKEN_CACHE_KEY);
        if ($cached) {
            return $cached;
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)->asJson()->post(self::TOKEN_URL, [
                'grant_type' => 'client_credentials',
                'client_id' => config('services.amazon_creators.credential_id'),
                'client_secret' => config('services.amazon_creators.credential_secret'),
                'scope' => 'creatorsapi::default',
            ]);
        } catch (\Throwable $e) {
            throw new CreatorsApiException('Could not reach Amazon for a token. Try again in a moment.');
        }

        $token = $response->successful() ? $response->json('access_token') : null;
        if (!$token) {
            // Only the status: a token error body isn't needed and could echo request details
            throw new CreatorsApiException('Amazon refused the token request (HTTP '.$response->status().').');
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, self::TOKEN_CACHE_SECONDS);

        return $token;
    }
}
