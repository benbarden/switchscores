<?php

namespace App\Domain\GameSearch;

/**
 * Cleans a title search term before it reaches the database.
 *
 * games.title is utf8mb3, which can't hold 4-byte characters such as emoji. MySQL can't compare
 * a utf8mb4 parameter holding one against the column, and fails the whole query (error 3988).
 * A title can't contain those characters, so removing them can't stop a real title matching.
 */
class SearchTerm
{
    public static function clean(mixed $value): string
    {
        // Scanners send arrays (search_keywords[]=x); only a plain value is a search term
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }

        $term = (string) $value;

        if (!mb_check_encoding($term, 'UTF-8')) {
            $term = mb_scrub($term, 'UTF-8');
        }

        $term = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '', $term) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $term) ?? '');
    }
}
