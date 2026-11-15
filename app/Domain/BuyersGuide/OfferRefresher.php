<?php

namespace App\Domain\BuyersGuide;

use App\Domain\AffiliateCodes\CreatorsApi;
use App\Domain\AffiliateCodes\CreatorsApiException;
use App\Domain\GameLists\Repository as GameListsRepository;

/**
 * Fetches the Amazon US offers for everything in the buyer's guide and saves them to the
 * OfferStore: the hardware's US codes from config/buyers-guide.php, then the picked games' US
 * codes.
 *
 * getItems takes 10 ASINs a request and Amazon allows about one request a second, so the
 * batches are spaced out.
 */
class OfferRefresher
{
    public function __construct(
        private CreatorsApi $api,
        private OfferStore $store,
        private GameListsRepository $repoGameLists,
        private int $pauseMicroseconds = 2_000_000,
    )
    {
    }

    /**
     * @return string[]
     */
    public function usAsins(): array
    {
        $asins = [];
        foreach (config('buyers-guide.hardware', []) as $item) {
            $asins[] = $item['asin_us'] ?? null;
        }

        $gameIds = array_merge(
            config('buyers-guide.games.switch-2', []),
            config('buyers-guide.games.switch-1', [])
        );
        if ($gameIds) {
            foreach ($this->repoGameLists->byIdList($gameIds) as $game) {
                if (!$game->isDelisted()) {
                    $asins[] = $game->amazon_us_asin;
                }
            }
        }

        $asins = array_filter($asins, fn($asin) => is_string($asin) && preg_match('/^[A-Z0-9]{10}$/', $asin));

        return array_values(array_unique($asins));
    }

    /**
     * Saves whatever came back, unless nothing did, so one failed batch only drops its own
     * items for the hour. Returns the counts and any batch errors.
     *
     * @return array{asked: int, returned: int, errors: string[]}
     */
    public function refresh(): array
    {
        $asins = $this->usAsins();
        $items = [];
        $errors = [];

        foreach (array_chunk($asins, CreatorsApi::GET_ITEMS_MAX) as $i => $batch) {
            if ($i > 0 && $this->pauseMicroseconds > 0) {
                usleep($this->pauseMicroseconds);
            }
            try {
                $items += $this->api->getItems($batch);
            } catch (CreatorsApiException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if ($items) {
            $this->store->put($items, now());
        }

        return ['asked' => count($asins), 'returned' => count($items), 'errors' => $errors];
    }
}
