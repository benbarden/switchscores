<?php

namespace App\Domain\AffiliateCodes;

/**
 * Checks on an Amazon product title against the game it's linked to: Find match on the Top rated
 * gaps list, and the nightly US link check, use the same rules.
 */
class ProductTitleCheck
{
    // Amazon writes "Nintendo Switch™ 2" as well as "Nintendo Switch 2"
    private const SWITCH_2 = '/switch\s*[™®]?\s*2/iu';

    private const SWITCH_ANY_BUT_2 = '/switch(?!\s*[™®]?\s*2)/iu';

    // Words Amazon US titles use for a listing from another region. "US Version" is fine.
    private const REGION_EDITION = '/\bnon[\s-]?us\b|\bimport\b|\b(?:european|europe|eu|uk|pal|japanese|japan|jp|asian|asia|australian|au)\s+version\b/iu';

    /**
     * Names the other console when the title suggests one, e.g. 'Switch 2' for a Switch 2 edition
     * found for a Switch 1 game. Null when the title doesn't suggest a mismatch.
     */
    public function consoleWarning(?string $title, bool $gameIsSwitch2): ?string
    {
        if ($title === null) {
            return null;
        }

        $namesSwitch2 = (bool) preg_match(self::SWITCH_2, $title);
        $namesSwitch1 = (bool) preg_match(self::SWITCH_ANY_BUT_2, $title);

        if (!$gameIsSwitch2 && $namesSwitch2) {
            return 'Switch 2';
        }

        if ($gameIsSwitch2 && $namesSwitch1 && !$namesSwitch2) {
            return 'Switch 1';
        }

        return null;
    }

    /**
     * True when the title says the listing is from another region, e.g. "(Non-US Version)",
     * "UK Import" or "(European Version)".
     */
    public function isRegionEdition(?string $title): bool
    {
        return $title !== null && (bool) preg_match(self::REGION_EDITION, $title);
    }
}
