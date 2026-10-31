<?php

namespace App\Domain\AffiliateCodes;

/**
 * Gets an ASIN from what staff paste in: the ASIN itself, or a full Amazon product URL.
 * Short amzn.to links hide the ASIN, so they can't be read.
 */
class AsinExtractor
{
    private const ASIN_ONLY = '#^[A-Z0-9]{10}/?$#i';

    private const PRODUCT_URL = '#^(?:https?://)?(?:[a-z0-9-]+\.)*amazon\.[a-z.]+/(?:[^?\#]*/)?'
        .'(?:dp|gp/product|gp/aw/d|product|ASIN)/([A-Z0-9]{10})(?=[/?\#]|$)#i';

    private const STORE_DOMAINS = ['us' => 'com', 'uk' => 'co.uk'];

    /**
     * True for a URL from a different Amazon store. Amazon can redirect a visitor to a "best bet"
     * product on their local store, and that page's ASIN is the wrong one to save.
     */
    public function isWrongStore(?string $value, string $region): bool
    {
        if (!preg_match('#^(?:https?://)?(?:[a-z0-9-]+\.)*amazon\.([a-z.]+)/#i', trim((string) $value), $matches)) {
            return false;
        }

        return strtolower($matches[1]) !== self::STORE_DOMAINS[$region];
    }

    /**
     * @return string|null The ASIN in upper case, or null if the value doesn't contain one.
     */
    public function extract(?string $value): ?string
    {
        $value = trim((string) $value);

        if (preg_match(self::ASIN_ONLY, $value)) {
            return strtoupper(rtrim($value, '/'));
        }

        if (preg_match(self::PRODUCT_URL, $value, $matches)) {
            return strtoupper($matches[1]);
        }

        return null;
    }
}
