<?php

namespace App\Domain\AffiliateCodes;

use App\Enums\AmazonLinkCheckResult;
use App\Models\Console;
use App\Models\Game;

/**
 * Checks linked games' Amazon US links through the Creators API and works out a result for each.
 * Saves nothing; the amazon:check-us-links command stores the results.
 *
 * Amazon rate-limits fast calls: half a second between requests got "too many requests" part way
 * through a full run, two seconds didn't. A batch that's still refused after the retries is
 * reported as failed and its games are left unchecked.
 */
class UsLinkChecker
{
    // Availability types that mean the product can't be bought. Any other type (in stock,
    // pre-order, a later date) can be.
    private const NOT_BUYABLE = ['OUT_OF_STOCK'];

    private const RETRIES = 3;

    public function __construct(
        private CreatorsApi $api,
        private ProductTitleCheck $titleCheck,
        private int $pauseMicroseconds = 2_000_000,
        private int $retryPauseMicroseconds = 5_000_000,
    )
    {
    }

    /**
     * @param iterable<Game> $games linked games with a US ASIN
     * @return array{results: array<int, array{result: AmazonLinkCheckResult, title: ?string}>, asked: int, failedBatches: string[]}
     *   results are keyed by game ID; title is Amazon's, for showing on the command output only
     */
    public function check(iterable $games): array
    {
        $gamesByAsin = [];
        foreach ($games as $game) {
            if ($game->amazon_us_asin) {
                $gamesByAsin[$game->amazon_us_asin][] = $game;
            }
        }

        $results = [];
        $failedBatches = [];

        foreach (array_chunk(array_keys($gamesByAsin), CreatorsApi::GET_ITEMS_MAX) as $i => $batch) {
            if ($i > 0) {
                $this->pause($this->pauseMicroseconds);
            }

            try {
                $response = $this->checkBatch($batch);
            } catch (CreatorsApiException $e) {
                $failedBatches[] = $e->getMessage();
                continue;
            }

            foreach ($batch as $asin) {
                $item = $response['items'][$asin] ?? null;
                $error = $response['errors'][$asin] ?? null;
                foreach ($gamesByAsin[$asin] as $game) {
                    $results[$game->id] = [
                        'result' => $this->result($game, $item, $error),
                        'title' => $item['title'] ?? null,
                    ];
                }
            }
        }

        return ['results' => $results, 'asked' => count($gamesByAsin), 'failedBatches' => $failedBatches];
    }

    /**
     * Broken first, then can't buy, then check by hand: a wrong-console link that's also out of
     * stock is reported as the wrong console.
     */
    public function result(Game $game, ?array $item, ?string $error): AmazonLinkCheckResult
    {
        if ($item === null) {
            return $error === 'ItemNotAccessible'
                ? AmazonLinkCheckResult::NOT_ACCESSIBLE
                : AmazonLinkCheckResult::INVALID;
        }

        $isSwitch2 = $game->console_id == Console::ID_SWITCH_2;
        if ($this->titleCheck->consoleWarning($item['title'] ?? null, $isSwitch2) !== null) {
            return AmazonLinkCheckResult::CONSOLE_MISMATCH;
        }

        $availability = $item['availability'] ?? [];
        if (!array_diff($availability, self::NOT_BUYABLE)) {
            // No listings at all, or every listing out of stock
            return AmazonLinkCheckResult::OUT_OF_STOCK;
        }

        if ($this->titleCheck->isRegionEdition($item['title'] ?? null)) {
            return AmazonLinkCheckResult::REGION_EDITION;
        }

        return AmazonLinkCheckResult::OK;
    }

    private function checkBatch(array $batch): array
    {
        for ($try = 0; ; $try++) {
            try {
                return $this->api->checkItems($batch);
            } catch (CreatorsApiRateLimitException $e) {
                if ($try >= self::RETRIES) {
                    throw $e;
                }
                $this->pause($this->retryPauseMicroseconds * ($try + 1));
            }
        }
    }

    private function pause(int $microseconds): void
    {
        if ($microseconds > 0) {
            usleep($microseconds);
        }
    }
}
