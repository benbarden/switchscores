<?php

namespace App\Domain\Steam;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

use App\Domain\Game\Repository as GameRepository;
use App\Models\SteamReviewData;

class SyncService
{
    public function __construct(
        private Repository $repoSteam,
        private GameRepository $repoGame
    )
    {
    }

    /**
     * Fetch Steam review data for a single game and store it.
     * Clears the game cache only if the data has changed.
     * Returns the stored SteamReviewData on success, null on failure.
     */
    public function syncGame(int $gameId, string $steamId): ?SteamReviewData
    {
        // store.steampowered.com/appreviews was deprecated and now redirects to the store homepage.
        // The review score fields are only returned on the first page; one review is the smallest page.
        $client = new GuzzleClient(['timeout' => 10]);
        $url = 'https://api.steampowered.com/IUserReviewsService/GetAppReviews/v1/';
        $input = [
            'appid' => (int) $steamId,
            'num_per_page' => 1,
            'languages' => ['all'],
        ];

        try {
            $response = $client->get($url, ['query' => ['input_json' => json_encode($input)]]);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException) {
            return null;
        }

        // No success field any more: Guzzle throws on 4xx/5xx (including 429), so check the summary.
        $summary = $data['response']['query_summary'] ?? null;
        if (empty($summary) || !isset($summary['total_reviews'])) {
            return null;
        }

        $changed = $this->repoSteam->upsertReviewData($gameId, $steamId, $summary);

        if ($changed) {
            $this->repoGame->clearCacheCoreData($gameId);
        }

        return $this->repoSteam->getReviewDataForGame($gameId);
    }
}
