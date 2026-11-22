<?php

namespace App\Enums;

/**
 * What the nightly Amazon US link check found for a linked game. Only our own result is stored,
 * never Amazon's content.
 */
enum AmazonLinkCheckResult: string
{
    case OK               = 'ok';
    case INVALID          = 'invalid';
    case CONSOLE_MISMATCH = 'console_mismatch';
    case OUT_OF_STOCK     = 'out_of_stock';
    case NOT_ACCESSIBLE   = 'not_accessible';
    case REGION_EDITION   = 'region_edition';

    const GROUP_BROKEN = 'broken';
    const GROUP_CANT_BUY = 'cant_buy';
    const GROUP_CHECK_BY_HAND = 'check_by_hand';

    /**
     * Broken links are definite problems. Can't buy may come back on its own. Check by hand is
     * often fine: "not accessible" usually means the API can't see the product, not that the page
     * has gone, and a region edition can be the best listing there is.
     */
    public function group(): ?string
    {
        return match ($this) {
            self::OK => null,
            self::INVALID, self::CONSOLE_MISMATCH => self::GROUP_BROKEN,
            self::OUT_OF_STOCK => self::GROUP_CANT_BUY,
            self::NOT_ACCESSIBLE, self::REGION_EDITION => self::GROUP_CHECK_BY_HAND,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::OK => 'OK',
            self::INVALID => 'Product not found',
            self::CONSOLE_MISMATCH => 'Wrong console',
            self::OUT_OF_STOCK => 'Out of stock',
            self::NOT_ACCESSIBLE => 'API can\'t see it',
            self::REGION_EDITION => 'Region edition',
        };
    }
}
