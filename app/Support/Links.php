<?php

namespace App\Support;

class Links
{
    /**
     * Build a Nintendo eShop URL from a region + path (or return an absolute URL untouched).
     *
     * @param string|null $region e.g. 'eu', 'us', 'jp'
     * @param string|null $url    e.g. '/store/products/xyz' or 'store/products/xyz' or full https URL
     * @return string|null
     */
    public static function eshopUrl(?string $region, ?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        // If it's already absolute, return as-is
        if (preg_match('~^https?://~i', $url)) {
            return $url;
        }

        // Normalise leading slash
        if ($url[0] !== '/') {
            $url = '/' . $url;
        }

        $r = strtolower((string)$region);

        switch ($r) {
            case 'eu':
            case 'us':
                return 'https://www.nintendo.com' . $url;
            case 'jp':
                return 'https://www.nintendo.co.jp' . $url;
            default:
                // Unknown region → return the normalised relative path (safe default)
                return $url;
        }
    }
}
