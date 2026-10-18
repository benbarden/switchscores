<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\DataSourcePrice;
use App\Models\Game;
use App\Models\GameImportRuleEshop;
use Carbon\Carbon;

/**
 * Decides a game's price from the per-SKU prices in data_source_prices (#162).
 *
 * The rule: the game's price is its standalone SKU (NSUID prefix 7001), never the
 * cheapest SKU on the store page. That is the whole fix for upgrade packs and deluxe
 * editions - see docs/tasks/switch-2-upgrade-pack-pricing.md. The discount comes from
 * the same SKU, so a sale on the deluxe edition or the upgrade pack is never shown
 * as a sale on the game.
 *
 * Runs AFTER DSNintendoCoUkUpdateGames and overwrites what it wrote, for Switch 2
 * only (Ben, 2026-10-18). The scalar-field price from the search payload stays as
 * the fallback for any game this class skips.
 *
 * decide() makes no writes, so a dry run and a real run take exactly the same path.
 */
class PriceApply
{
    /**
     * sales_status values whose regular_price is the game's real price. A pre-order or
     * an unreleased game has a price on the store page; a terminated SKU no longer sells.
     */
    const USABLE_STATUSES = [
        NsuidPrice::STATUS_ON_SALE,
        NsuidPrice::STATUS_UNRELEASED,
        'preorder',
    ];

    /**
     * @param  DataSourcePrice[]  $skuPrices  stored prices for the NSUIDs in the game's raw record
     * @param  int  $maxAgeHours  a 7001 price fetched longer ago than this is not trusted:
     *                            it may carry a sale that has since ended
     */
    public function decide(
        Game $game,
        array $skuPrices,
        ?GameImportRuleEshop $rule,
        Carbon $now,
        int $maxAgeHours
    ): PriceDecision {
        $oldPrice      = $this->money($game->price_eshop);
        $oldDiscounted = $this->money($game->price_eshop_discounted);
        $oldDiscountPc = $this->percent($game->price_eshop_discount_pc);

        $standalone = array_values(array_filter($skuPrices, fn (DataSourcePrice $p) =>
            str_starts_with($p->nsuid, NsuidPrice::PREFIX_GAME)
            && in_array($p->sales_status, self::USABLE_STATUSES, true)
            && !is_null($p->regular_price)
        ));

        if (empty($standalone)) {
            return new PriceDecision(PriceDecision::NO_API_PRICE, oldPrice: $oldPrice);
        }

        // Two 7001 SKUs on one record at the same price is harmless; at different
        // prices there is no rule yet for which one is the game.
        $distinct = array_unique(array_map(fn ($p) => $this->money($p->regular_price), $standalone));
        if (count($distinct) > 1) {
            return new PriceDecision(PriceDecision::AMBIGUOUS, nsuid: $standalone[0]->nsuid, oldPrice: $oldPrice);
        }

        $sku = $standalone[0];

        if (is_null($sku->fetched_at) || Carbon::parse($sku->fetched_at)->lt($now->copy()->subHours($maxAgeHours))) {
            return new PriceDecision(PriceDecision::STALE, nsuid: $sku->nsuid, oldPrice: $oldPrice);
        }

        $newPrice = $this->money($sku->regular_price);

        // Same guard as UpdateGame::updatePrice(): don't replace a real price with zero.
        // Kept until the examples show whether the price API's zeros are genuine.
        if ((float) $newPrice == 0 && (float) $oldPrice > 0) {
            return new PriceDecision(PriceDecision::API_ZERO, nsuid: $sku->nsuid, oldPrice: $oldPrice, newPrice: $newPrice);
        }

        [$newDiscounted, $newDiscountPc] = $this->discount($sku, $now);

        $decision = fn (string $outcome) => new PriceDecision(
            $outcome, $sku->nsuid,
            $oldPrice, $newPrice,
            $oldDiscounted, $newDiscounted,
            $oldDiscountPc, $newDiscountPc
        );

        if ($rule && $rule->shouldIgnorePrice()) {
            return $decision(PriceDecision::FROZEN);
        }

        $result = $decision(PriceDecision::CHANGED);

        return $result->differs() ? $result : $decision(PriceDecision::UNCHANGED);
    }

    /**
     * Writes a CHANGED decision to the game. Does not save.
     */
    public function apply(Game $game, PriceDecision $decision): void
    {
        if ($decision->outcome !== PriceDecision::CHANGED) {
            return;
        }

        $game->price_eshop             = $decision->newPrice;
        $game->price_eshop_discounted  = $decision->newDiscounted;
        $game->price_eshop_discount_pc = $decision->newDiscountPc;
    }

    /**
     * The SKU's live discount as [discounted price, percent], or [null, null].
     *
     * The percent is worked out from the two prices, to 1dp, because the price API
     * doesn't send one. That matches the search payload's own figures (e.g. 90.1 for
     * 9.99 -> 0.99).
     */
    private function discount(DataSourcePrice $sku, Carbon $now): array
    {
        if (is_null($sku->discount_price)) {
            return [null, null];
        }

        if (!is_null($sku->discount_end_at) && Carbon::parse($sku->discount_end_at)->lte($now)) {
            return [null, null];
        }
        if (!is_null($sku->discount_start_at) && Carbon::parse($sku->discount_start_at)->gt($now)) {
            return [null, null];
        }

        $regular  = (float) $sku->regular_price;
        $discount = (float) $sku->discount_price;

        if ($regular <= 0 || $discount >= $regular) {
            return [null, null];
        }

        return [
            $this->money($discount),
            $this->percent((1 - $discount / $regular) * 100),
        ];
    }

    private function money(string|float|null $value): ?string
    {
        return is_null($value) || $value === '' ? null : number_format((float) $value, 2, '.', '');
    }

    private function percent(string|float|null $value): ?string
    {
        return is_null($value) || $value === '' ? null : number_format(round((float) $value, 1), 1, '.', '');
    }
}
