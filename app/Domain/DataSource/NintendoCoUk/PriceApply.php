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
 * Switch 2 only. Called from UpdateGame::updatePrice() via UpdateGameFactory (#183),
 * so DSNintendoCoUkUpdateGames writes each price once; DSNintendoCoUkApplyPrices runs
 * the same decision afterwards as the nightly report. The scalar-field price from the
 * search payload stays as the fallback for any game this class skips.
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

        // A free 7001 next to a paid one is a Friend's Pass or free trial, not the game:
        // Orbitals, Split Fiction, Little Nightmares III and Lego Voyagers, checked on the
        // store 2026-10-18. A game whose only 7001 SKUs are free keeps them - it's free.
        $paid = array_values(array_filter($standalone, fn (DataSourcePrice $p) => (float) $p->regular_price > 0));
        if (!empty($paid)) {
            $standalone = $paid;
        }

        // Two paid 7001 SKUs on one record at the same price is harmless; at different
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

        [$newDiscounted, $newDiscountPc] = $this->discount($sku, $now, $oldDiscountPc);

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
     * The price API doesn't send a percent, so it is worked out from the two prices and
     * rounded to a whole number: the store shows whole percents (37%, 40%, 60%), and the
     * search payload's figure matches the whole-number rounding for 1,232 of 1,346
     * Switch 1 discounts against 601 for 1dp. The stored percent is kept when it is
     * within 0.5 of the worked-out one, so the payload's occasional 1dp figures (90.1
     * for 17.99 -> 1.79) don't flip back and forth between UpdateGames and this class
     * every night.
     */
    private function discount(DataSourcePrice $sku, Carbon $now, ?string $oldDiscountPc): array
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

        $exact = (1 - $discount / $regular) * 100;

        if (!is_null($oldDiscountPc) && abs($exact - (float) $oldDiscountPc) < 0.5) {
            return [$this->money($discount), $oldDiscountPc];
        }

        return [$this->money($discount), $this->percent(round($exact))];
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
