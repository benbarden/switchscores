<?php

namespace App\Domain\DataSource\NintendoCoUk;

/**
 * The outcome of a PriceLookup::fetch() call.
 *
 * Carries the prices AND the counts, because the counts are what the import run
 * reporting needs. The failure path for this integration is "fall back silently to
 * the scalar price fields", which is right for an endpoint with no SLA but means a
 * degrading endpoint would otherwise be invisible - prices would quietly go back to
 * being wrong in exactly the way this integration exists to fix, with nothing on
 * screen to say so. These counts are what make that visible.
 */
class PriceLookupResult
{
    /** @var array<string, NsuidPrice> keyed by NSUID */
    private array $prices = [];

    private int $calls = 0;
    private int $failedBatches = 0;
    private int $notFound = 0;

    public function __construct(public readonly int $requested = 0)
    {
    }

    public function add(NsuidPrice $price): void
    {
        $this->prices[$price->nsuid] = $price;

        if (!$price->isResolved()) {
            $this->notFound++;
        }
    }

    public function recordCall(): void
    {
        $this->calls++;
    }

    public function recordFailedBatch(): void
    {
        $this->failedBatches++;
    }

    public function get(string $nsuid): ?NsuidPrice
    {
        $price = $this->prices[$nsuid] ?? null;

        // An unresolved SKU is reported as absent. Callers want "do I have a usable
        // price for this NSUID?", and a not_found row has no price on it - handing it
        // back would just push the same check into every caller.
        if ($price && !$price->isResolved()) {
            return null;
        }

        return $price;
    }

    /**
     * The first standalone-game SKU (70010000...) among the given NSUIDs.
     *
     * This is the price we want: for a Switch 2 Edition with an upgrade pack, the
     * scalar price fields hold the upgrade price instead.
     *
     * @param  string[]  $nsuids
     */
    public function findStandaloneGame(array $nsuids): ?NsuidPrice
    {
        foreach ($nsuids as $nsuid) {
            $price = $this->get((string) $nsuid);

            if ($price && $price->isStandaloneGame()) {
                return $price;
            }
        }

        return null;
    }

    /** @return array<string, NsuidPrice> */
    public function all(): array
    {
        return $this->prices;
    }

    public function resolved(): int
    {
        return count($this->prices) - $this->notFound;
    }

    public function notFound(): int
    {
        return $this->notFound;
    }

    public function calls(): int
    {
        return $this->calls;
    }

    public function failedBatches(): int
    {
        return $this->failedBatches;
    }

    /**
     * NSUIDs that were asked for but came back with nothing at all - the difference
     * between what went out and what came back, which is mostly failed batches.
     */
    public function missing(): int
    {
        return max(0, $this->requested - count($this->prices));
    }

    public function hasFailures(): bool
    {
        return $this->failedBatches > 0;
    }
}
