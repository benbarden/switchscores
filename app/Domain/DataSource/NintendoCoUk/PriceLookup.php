<?php

namespace App\Domain\DataSource\NintendoCoUk;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads per-SKU prices from the Nintendo eShop price API.
 *
 * This is the endpoint the eShop itself calls. It is UNOFFICIAL and UNDOCUMENTED:
 * no SLA, and it can change or disappear without notice. Everything here is built
 * on that assumption - a failed batch is counted and skipped, never fatal, so the
 * caller can fall back to the search payload's scalar price fields.
 *
 * Why this exists: the search payload carries only scalar price fields
 * (price_regular_f / price_sorting_f), and for a Switch 2 Edition with an upgrade
 * pack those hold the UPGRADE price rather than the game's. The scalar fields have
 * no field that distinguishes the two. This endpoint returns prices per NSUID, and
 * the NSUID prefix is the discriminator - see NsuidPrice::PREFIX_*.
 *
 * Full analysis: docs/tasks/switch-2-upgrade-pack-pricing.md
 */
class PriceLookup
{
    private const PRICE_URL = 'https://api.ec.nintendo.com/v1/price';

    /**
     * NSUIDs per request. The endpoint accepts multiple ids in one call; 50 is the
     * figure community price trackers use and what the 2026-07-20 sweep ran at
     * (583 NSUIDs in ~12 calls, no failures). Not a probed ceiling - it is a
     * known-good value, and there is no reason to go looking for the real limit
     * on an endpoint with no SLA.
     */
    public const BATCH_SIZE = 50;

    private const COUNTRY = 'GB';
    private const LANG    = 'en';

    private const TIMEOUT_SECONDS = 20;

    public function __construct(private readonly ?\Closure $sleeper = null)
    {
    }

    /**
     * Look up prices for a set of NSUIDs.
     *
     * Never throws on an API problem. A batch that fails is recorded in the result's
     * failedBatches count and its NSUIDs simply do not appear in prices() - the
     * caller treats an absent NSUID and a failed one the same way, by falling back.
     *
     * @param  string[]  $nsuids
     */
    public function fetch(array $nsuids): PriceLookupResult
    {
        // Normalise first: callers pass NSUIDs straight out of nsuid_txt, which can
        // carry duplicates (the same SKU listed against several records) and empties.
        $nsuids = array_values(array_unique(array_filter(array_map(
            fn ($nsuid) => trim((string) $nsuid),
            $nsuids
        ), fn ($nsuid) => $nsuid !== '')));

        $result = new PriceLookupResult(requested: count($nsuids));

        if (empty($nsuids)) {
            return $result;
        }

        foreach (array_chunk($nsuids, self::BATCH_SIZE) as $batchIndex => $batch) {
            if ($batchIndex > 0) {
                $this->pause();
            }

            $this->fetchBatch($batch, $result);
        }

        return $result;
    }

    private function fetchBatch(array $batch, PriceLookupResult $result): void
    {
        $result->recordCall();

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)->get(self::PRICE_URL, [
                'country' => self::COUNTRY,
                'lang'    => self::LANG,
                'ids'     => implode(',', $batch),
            ]);
        } catch (\Throwable $e) {
            // Connection refused, DNS failure, timeout.
            Log::warning('Price API request failed: '.$e->getMessage());
            $result->recordFailedBatch();
            return;
        }

        if (!$response->successful()) {
            Log::warning('Price API returned HTTP '.$response->status());
            $result->recordFailedBatch();
            return;
        }

        $prices = $response->json('prices');

        if (!is_array($prices)) {
            // 200 with a body we do not recognise. Treated as a failed batch rather
            // than an empty one: "the endpoint changed shape" and "these SKUs have no
            // price" are very different facts and must not be conflated.
            Log::warning('Price API returned an unexpected body shape');
            $result->recordFailedBatch();
            return;
        }

        foreach ($prices as $item) {
            if (!is_array($item)) {
                continue;
            }

            $price = NsuidPrice::fromApiItem($item);

            if ($price->nsuid === '') {
                continue;
            }

            $result->add($price);
        }
    }

    /**
     * Be polite between batches. This is someone else's undocumented endpoint and a
     * full run is only a dozen or so calls, so the wait costs nothing worth having.
     */
    private function pause(): void
    {
        if ($this->sleeper) {
            ($this->sleeper)();
            return;
        }

        usleep(250000);
    }
}
