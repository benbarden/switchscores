<?php

namespace App\Domain\GameGroup;

/**
 * Builds a comparison key from a game title, so the Switch 1 and Switch 2 versions of a
 * game can be matched when their titles differ only in case, punctuation or a console
 * suffix (#44). For matching only - never shown or saved as a title. Display clean-up of
 * imported titles is WeeklyBatch\TitleNormaliser, a different job.
 *
 * Word boundaries are kept on purpose: with all punctuation stripped, "Dragon Quest I & II"
 * becomes "dragonquestiii" and matches "Dragon Quest III".
 */
class TitleMatchKey
{
    // Checked in order, first match wins, so longer forms come before the shorter forms
    // they end with.
    private const CONSOLE_SUFFIXES = [
        ': Nintendo Switch 2 Edition',
        ' - Nintendo Switch 2 Edition',
        ' Nintendo Switch 2 Edition',
        ' Switch 2 Edition',
        ' (Nintendo Switch 2 Edition)',
        ' (Switch 2 Edition)',
        ' for Nintendo Switch 2',
        ' (Switch 2)',
    ];

    public function forTitle(string $title): string
    {
        $key = $this->stripConsoleSuffix(trim($title));
        $key = mb_strtolower($key);

        // Apostrophes join rather than split, so "Meier's" and "Meiers" agree
        $key = str_replace(["'", "\u{2018}", "\u{2019}", "\u{201B}"], '', $key);

        $key = str_replace(['™', '®'], '', $key);
        $key = str_replace('&', ' and ', $key);

        // Anything else that isn't a letter or digit is a word break
        $key = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $key);

        return trim($key);
    }

    /**
     * Removes a trailing "Nintendo Switch 2 Edition" / "(Switch 2)" style suffix, keeping
     * the original case. Returns the title unchanged if there isn't one.
     */
    public function stripConsoleSuffix(string $title): string
    {
        foreach (self::CONSOLE_SUFFIXES as $suffix) {
            $pattern = '/'.$this->suffixPattern($suffix).'$/iu';
            if (preg_match($pattern, $title)) {
                return preg_replace($pattern, '', $title);
            }
        }

        return $title;
    }

    /**
     * Nintendo's API titles write the suffix as "– Nintendo Switch™ 2 Edition", so a hyphen in a
     * suffix also matches an en or em dash, and "Switch" may carry ™ or ®. Only the suffix is
     * matched loosely: the rest of the title is returned as it was.
     */
    private function suffixPattern(string $suffix): string
    {
        $pattern = preg_quote($suffix, '/');
        $pattern = str_replace('\-', '[-\x{2013}\x{2014}]', $pattern);
        return str_replace('Switch', 'Switch[™®]?', $pattern);
    }
}
