<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\DataSourcePrice;
use App\Models\DataSourcePriceHistory;
use Carbon\Carbon;

/**
 * Persists a PriceLookupResult into data_source_prices, appending to
 * data_source_price_history whenever a price actually changed.
 *
 * Kept separate from PriceLookup so that fetching (network, no writes) and storing
 * (writes, no network) can be tested and reasoned about independently. PriceLookup
 * has no idea this class exists.
 */
class PriceStore
{
    /**
     * @param  ?int  $consoleId  recorded on new rows so Switch 1 and Switch 2 prices
     *                           stay separable once Switch 1 is added (#132).
     */
    public function store(PriceLookupResult $result, ?int $consoleId = null): PriceStoreResult
    {
        $outcome = new PriceStoreResult();
        $now = Carbon::now();

        // all() rather than the resolved-only view: a not_found SKU is worth recording.
        // It is a positive fact ("this id is dead") and the alternative is a row that
        // silently keeps its last known price forever with a stale fetched_at.
        foreach ($result->all() as $price) {
            $this->storeOne($price, $consoleId, $now, $outcome);
        }

        return $outcome;
    }

    private function storeOne(
        NsuidPrice $price,
        ?int $consoleId,
        Carbon $now,
        PriceStoreResult $outcome
    ): void {
        $existing = DataSourcePrice::where('nsuid', $price->nsuid)->first();

        $newRegular  = $this->normalise($price->regularPrice);
        $newDiscount = $this->normalise($price->discountPrice);

        if (!$existing) {
            DataSourcePrice::create([
                'nsuid'             => $price->nsuid,
                'console_id'        => $consoleId,
                'sales_status'      => $price->salesStatus,
                'regular_price'     => $newRegular,
                'discount_price'    => $newDiscount,
                'discount_start_at' => $this->toDateTime($price->discountStart),
                'discount_end_at'   => $this->toDateTime($price->discountEnd),
                'first_seen_at'     => $now,
                'fetched_at'        => $now,
                'price_changed_at'  => null,
            ]);

            // Deliberately NOT a history row. The first sighting is not a change, and
            // logging it as one would make "this price changed" untrustworthy on the
            // first run - when every SKU is new.
            $outcome->recordCreated();
            return;
        }

        $oldRegular  = $this->normalise($existing->regular_price);
        $oldDiscount = $this->normalise($existing->discount_price);

        $regularChanged  = $oldRegular !== $newRegular;
        $discountChanged = $oldDiscount !== $newDiscount;
        $changed = $regularChanged || $discountChanged;

        if ($changed) {
            DataSourcePriceHistory::create([
                'nsuid'              => $price->nsuid,
                'old_regular_price'  => $oldRegular,
                'new_regular_price'  => $newRegular,
                'old_discount_price' => $oldDiscount,
                'new_discount_price' => $newDiscount,
                'recorded_at'        => $now,
            ]);

            $existing->price_changed_at = $now;
        }

        $existing->sales_status      = $price->salesStatus;
        $existing->regular_price     = $newRegular;
        $existing->discount_price    = $newDiscount;
        $existing->discount_start_at = $this->toDateTime($price->discountStart);
        $existing->discount_end_at   = $this->toDateTime($price->discountEnd);
        $existing->fetched_at        = $now;

        if (is_null($existing->console_id) && !is_null($consoleId)) {
            $existing->console_id = $consoleId;
        }

        $existing->save();

        $changed ? $outcome->recordChanged() : $outcome->recordUnchanged();
    }

    /**
     * Normalise a money value to a 2dp string, or null.
     *
     * Both sides of the comparison go through this. Without it "13.5" from the API and
     * "13.50" out of a DECIMAL column read as a change, and every run would append a
     * history row for a price that never moved - which would quietly destroy the value
     * of the history table.
     */
    private function normalise(string|float|null $value): ?string
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    private function toDateTime(?string $value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
