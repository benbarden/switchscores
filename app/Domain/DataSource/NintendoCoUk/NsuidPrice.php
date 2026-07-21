<?php

namespace App\Domain\DataSource\NintendoCoUk;

/**
 * One SKU's prices as returned by the Nintendo eShop price API.
 *
 * Prices are kept as STRINGS exactly as the API sends them (raw_value, e.g. "13.49").
 * They are money, they go into decimal columns, and the parser formats them with
 * number_format() at the end - so there is nothing to gain from a float here and
 * a rounding error to lose.
 */
class NsuidPrice
{
    /**
     * sales_status values seen from the API.
     *
     * IMPORTANT: ON_SALE means "purchasable", NOT "discounted". Almost every live
     * SKU is `onsale` whether or not it has a discount. The discount signal is the
     * PRESENCE OF discount_price, which is what hasDiscount() tests. Reading
     * sales_status as the discount signal would mark the whole catalogue as on sale.
     */
    const STATUS_ON_SALE           = 'onsale';
    const STATUS_NOT_FOUND         = 'not_found';
    const STATUS_UNRELEASED        = 'unreleased';
    const STATUS_SALES_TERMINATION = 'sales_termination';

    /**
     * NSUID prefixes. The prefix is the discriminator that could not be found in the
     * search payload's scalar price fields - see
     * docs/tasks/switch-2-upgrade-pack-pricing.md.
     */
    const PREFIX_GAME    = '70010000'; // the standalone game - the price we want
    const PREFIX_UPGRADE = '70050000'; // upgrade pack
    const PREFIX_DELUXE  = '70070000'; // deluxe / premium edition bundle

    public function __construct(
        public readonly string  $nsuid,
        public readonly string  $salesStatus,
        public readonly ?string $regularPrice = null,
        public readonly ?string $discountPrice = null,
        public readonly ?string $discountStart = null,
        public readonly ?string $discountEnd = null,
    ) {
    }

    public static function fromApiItem(array $item): self
    {
        // title_id arrives as a JSON number, so json_decode gives an int. NSUIDs are
        // 14-digit identifiers, not quantities - cast to string so they can be used as
        // array keys and compared against nsuid_txt without surprises.
        $nsuid = (string) ($item['title_id'] ?? '');

        $discount = $item['discount_price'] ?? null;

        return new self(
            nsuid:         $nsuid,
            salesStatus:   (string) ($item['sales_status'] ?? ''),
            regularPrice:  isset($item['regular_price']['raw_value'])
                               ? (string) $item['regular_price']['raw_value']
                               : null,
            discountPrice: isset($discount['raw_value']) ? (string) $discount['raw_value'] : null,
            discountStart: $discount['start_datetime'] ?? null,
            discountEnd:   $discount['end_datetime'] ?? null,
        );
    }

    /**
     * Does this SKU have a live discount?
     *
     * Tests for the discount_price block, NOT sales_status - see the note on the
     * status constants above.
     */
    public function hasDiscount(): bool
    {
        return !is_null($this->discountPrice);
    }

    /**
     * Did the API actually know about this NSUID?
     *
     * A not_found SKU comes back in the response with no regular_price at all, so it
     * is a positive answer ("this id does not exist") rather than a silent omission.
     */
    public function isResolved(): bool
    {
        return $this->salesStatus !== self::STATUS_NOT_FOUND && !is_null($this->regularPrice);
    }

    public function isStandaloneGame(): bool
    {
        return str_starts_with($this->nsuid, self::PREFIX_GAME);
    }

    public function isUpgradePack(): bool
    {
        return str_starts_with($this->nsuid, self::PREFIX_UPGRADE);
    }

    public function isDeluxeEdition(): bool
    {
        return str_starts_with($this->nsuid, self::PREFIX_DELUXE);
    }
}
