<?php

namespace App\Domain\Steam;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

use App\Domain\Game\Repository as GameRepository;
use App\Models\SteamReviewData;

class SyncService
{
    private ?string $lastError = null;

    public function __construct(
        private Repository $repoSteam,
        private GameRepository $repoGame
    )
    {
    }

    /**
     * Why the last syncGame() call failed, or null if it succeeded.
     */
    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Fetch Steam review data for a single game and store it.
     * Clears the game cache only if the data has changed.
     * Returns the stored SteamReviewData on success, null on failure (see lastError()).
     * Every call records an attempt on the game, so a failing game moves back in the queue.
     */
    public function syncGame(int $gameId, string $steamId): ?SteamReviewData
    {
        $this->lastError = null;
        $this->repoSteam->markAttempted($gameId);

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
        } catch (GuzzleException $e) {
            $this->lastError = $e->getMessage();
            return null;
        }

        // No success field any more: Guzzle throws on 4xx/5xx (including 429), so check the summary.
        $summary = $data['response']['query_summary'] ?? null;
        if (empty($summary) || !isset($summary['total_reviews'])) {
            $this->lastError = 'No query_summary in the response (HTTP '.$response->getStatusCode().')';
            return null;
        }

        $changed = $this->repoSteam->upsertReviewData($gameId, $steamId, $summary);

        if ($changed) {
            $this->repoGame->clearCacheCoreData($gameId);
        }

        return $this->repoSteam->getReviewDataForGame($gameId);
    }
}
