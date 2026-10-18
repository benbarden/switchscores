<?php

namespace App\Domain\DataSource\NintendoCoUk;

/**
 * What PriceApply decided for one game, and the values it would write.
 *
 * Every outcome other than CHANGED leaves the game alone. The skip outcomes are kept
 * distinct rather than lumped together because each one is a different thing to
 * look at: FROZEN is a freeze that may no longer be needed, AMBIGUOUS is a record
 * the NSUID rule can't settle, API_ZERO is a price the guard refused.
 */
class PriceDecision
{
    const CHANGED      = 'changed';
    const UNCHANGED    = 'unchanged';
    const FROZEN       = 'frozen';        // ignore_price rule - never written, reported only
    const NO_API_PRICE = 'no-api-price';  // no usable 7001 SKU for this record
    const AMBIGUOUS    = 'ambiguous';     // more than one paid 7001 SKU, at different prices
    const STALE        = 'stale';         // the 7001 price wasn't fetched recently
    const API_ZERO     = 'api-zero';      // API says 0.00 for a game we hold a price for

    public function __construct(
        public readonly string  $outcome,
        public readonly ?string $nsuid = null,
        public readonly ?string $oldPrice = null,
        public readonly ?string $newPrice = null,
        public readonly ?string $oldDiscounted = null,
        public readonly ?string $newDiscounted = null,
        public readonly ?string $oldDiscountPc = null,
        public readonly ?string $newDiscountPc = null,
    ) {
    }

    /**
     * Would the API values differ from what the game holds?
     *
     * True for CHANGED, and for a FROZEN game whose freeze is holding back a different
     * price - which is the case worth reviewing before freezes are removed.
     */
    public function differs(): bool
    {
        if (is_null($this->newPrice)) {
            return false;
        }

        return $this->oldPrice !== $this->newPrice
            || $this->oldDiscounted !== $this->newDiscounted
            || $this->oldDiscountPc !== $this->newDiscountPc;
    }
}
