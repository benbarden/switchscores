<?php

namespace App\Domain\AffiliateCodes;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Amazon Creators API client, for staff product searches on Amazon US.
 *
 * Results are shown to staff to pick from and never stored: only the ASIN chosen is saved,
 * through the usual row save. The credential and token must never be logged or shown.
 */
class CreatorsApi
{
    const TOKEN_URL = 'https://api.amazon.com/auth/o2/token';

    const SEARCH_URL = 'https://creatorsapi.amazon/catalog/v1/searchItems';

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
     * The buy box price if there is one, otherwise the first listing's.
     */
    private function price(array $listings): ?string
    {
        foreach ($listings as $listing) {
            if (!empty($listing['isBuyBoxWinner'])) {
                return $listing['price']['money']['displayAmount'] ?? null;
            }
        }

        return $listings[0]['price']['money']['displayAmount'] ?? null;
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
