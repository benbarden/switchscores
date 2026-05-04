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
        $client = new GuzzleClient(['timeout' => 10]);
        $url = 'https://store.steampowered.com/appreviews/' . $steamId
            . '?json=1&num_per_page=0&language=all';

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException) {
            return null;
        }

        if (empty($data['success']) || empty($data['query_summary'])) {
            return null;
        }

        $changed = $this->repoSteam->upsertReviewData($gameId, $steamId, $data['query_summary']);

        if ($changed) {
            $this->repoGame->clearCacheCoreData($gameId);
        }

        return $this->repoSteam->getReviewDataForGame($gameId);
    }
}
