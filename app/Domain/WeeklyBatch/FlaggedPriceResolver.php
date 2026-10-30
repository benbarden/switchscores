<?php

namespace App\Domain\WeeklyBatch;

use App\Domain\DataSource\NintendoCoUk\NsuidPrice;
use App\Domain\DataSource\NintendoCoUk\PriceApply;
use App\Domain\DataSource\NintendoCoUk\PriceLookup;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Models\WeeklyBatchItem;

/**
 * Settles flagged weekly update prices from the price API, so a new game is added at its real
 * price and the Prices step only asks about what's left. The price API takes any NSUID, so this
 * works for games with no API record yet, which the nightly run can't reach.
 *
 * Two points in the flow, because the paste only gives one NSUID per game:
 * - After a page is parsed: a pasted 7001 is the game itself, so its price is the answer.
 * - After a store page is fetched: for a pasted 7005 (upgrade pack) or 7007 (deluxe edition or
 *   bundle), and for a 7001 at £0, the page lists the game's 7001 SKUs.
 *
 * Anything unsettled keeps its flag. PriceLookup never throws on an API problem, so a failed
 * lookup just leaves the flags as they were.
 */
class FlaggedPriceResolver
{
    public function __construct(
        private PriceLookup $priceLookup,
        private WeeklyBatchItemRepository $repoItem
    ) {
    }

    /**
     * Settle flagged items from their pasted NSUID. A £0 keeps its flag here: one SKU can't
     * show whether the game is free or that's a Friend's Pass, and the store page can.
     *
     * @return int how many items were settled
     */
    public function resolvePasted(iterable $items): int
    {
        $flagged = collect($items)->filter(fn(WeeklyBatchItem $item) =>
            $item->price_flag && str_starts_with((string) $item->nsuid, NsuidPrice::PREFIX_GAME));

        if ($flagged->isEmpty()) {
            return 0;
        }

        $result = $this->priceLookup->fetch($flagged->pluck('nsuid')->unique()->values()->all());
        $resolved = 0;

        foreach ($flagged as $item) {
            $price = $result->get($item->nsuid);
            if ($price && $this->isUsable($price) && (float) $price->regularPrice > 0) {
                $this->repoItem->updatePriceFromApi($item, (float) $price->regularPrice);
                $resolved++;
            }
        }

        return $resolved;
    }

    /**
     * Settle a flagged item from the 7001 SKUs on its store page, by the same rule as
     * PriceApply: a free SKU next to a paid one is a Friend's Pass or trial, only-free SKUs
     * mean the game is free, and paid SKUs at different prices are left for a person.
     *
     * @param  string[]  $nsuids
     */
    public function resolveFromStorePage(WeeklyBatchItem $item, array $nsuids): bool
    {
        if (!$item->price_flag || empty($nsuids)) {
            return false;
        }

        $result = $this->priceLookup->fetch($nsuids);

        $amounts = [];
        foreach ($nsuids as $nsuid) {
            $price = $result->get($nsuid);
            if ($price && $this->isUsable($price)) {
                $amounts[] = (float) $price->regularPrice;
            }
        }

        $paid = array_filter($amounts, fn(float $amount) => $amount > 0);
        $distinct = array_values(array_unique($paid ?: $amounts));

        if (count($distinct) !== 1) {
            return false;
        }

        $this->repoItem->updatePriceFromApi($item, $distinct[0]);

        return true;
    }

    private function isUsable(NsuidPrice $price): bool
    {
        return $price->isResolved() && in_array($price->salesStatus, PriceApply::USABLE_STATUSES, true);
    }
}
