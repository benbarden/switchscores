<?php

namespace App\Domain\DataSource\NintendoCoUk;

use App\Models\Console;

/**
 * Builds a link to the Nintendo.co.uk website search for a game title, filtered to its console.
 *
 * Spaces must be sent as %20: the search box shows a "+" literally, so "Tri+Breaker" stays
 * as typed and finds nothing. The staff game editor's "Search Nintendo" button builds the
 * same URL in JavaScript (resources/views/staff/games/editor/scripts.twig) - keep them in step.
 */
class SearchUrl
{
    const BASE = 'https://www.nintendo.com/en-gb/Search/Search-299117.html';

    const FILTER_SWITCH_1 = '147394-5-86';
    const FILTER_SWITCH_2 = '147394-91-126551';

    public static function forTitle(string $title, int $consoleId): string
    {
        $filter = $consoleId == Console::ID_SWITCH_2 ? self::FILTER_SWITCH_2 : self::FILTER_SWITCH_1;

        return self::BASE.'?q='.rawurlencode(self::searchTerms($title)).'&f='.$filter;
    }

    /**
     * Colons and separating dashes become spaces, then runs of spaces collapse to one.
     * Hyphens inside a word ("HD-2D") are kept.
     */
    public static function searchTerms(string $title): string
    {
        $terms = str_replace(':', ' ', $title);
        $terms = preg_replace('/\s[-\x{2013}\x{2014}]\s/u', ' ', $terms);
        $terms = preg_replace('/\s+/u', ' ', $terms);
        return trim($terms);
    }
}
