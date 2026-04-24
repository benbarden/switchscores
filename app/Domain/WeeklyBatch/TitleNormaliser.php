<?php

namespace App\Domain\WeeklyBatch;

class TitleNormaliser
{
    private const MINOR_WORDS = [
        'a', 'an', 'the', 'and', 'but', 'or', 'nor', 'for', 'yet', 'so',
        'at', 'by', 'in', 'of', 'on', 'to', 'up', 'as', 'is', 'it',
    ];

    // ALL CAPS abbreviations that should never be title-cased
    private const KNOWN_CAPS = [
        'RPG', 'JRPG', 'TRPG', 'MMORPG', 'MMO',
        'DLC', 'NPC', 'FPS', 'TPS', 'HUD',
        'AI', 'VR', 'AR', 'PC', 'UI',
        'HD', 'SD',
        'USA', 'UK', 'EU', 'JP', 'US',
        'SNK', 'NIS', 'ACA',
        'GBA', 'GBC', 'NES', 'SNES',
        'MSX', 'MSX2',
    ];

    public function normalise(string $title): string
    {
        // Remove trademark/registered symbols
        $title = str_replace(['™', '®'], '', $title);
        $title = trim($title);

        // "EGGCONSOLE" → "Egg Console" (before ALL CAPS detection)
        $title = preg_replace('/\bEGGCONSOLE\b/i', 'Egg Console', $title);

        // Normalize & → " AND " (uppercase) before ALL CAPS detection so "CARD&CASINO" splits correctly
        // lowercaseMinorWords will bring "AND" back to "and" after title case conversion
        $title = preg_replace('/ *& */', ' AND ', $title);

        // ALL CAPS → Title Case (if the majority of multi-letter words are uppercase)
        $wasAllCaps = $this->isAllCaps($title);
        if ($wasAllCaps) {
            $title = $this->toTitleCase($title);
        }

        // Hyphen-wrapped subtitle: "Title -Subtitle-" → "Title: Subtitle"
        $title = preg_replace('/\s+-([^-]+)-$/', ': $1', $title);

        // Tilde as separator: "A ~B~" → "A: B" (opening and closing tildes, with whitespace before)
        $title = preg_replace('/\s+~(.+?)~$/', ': $1', $title);

        // Clean up any remaining tildes: replace with space, then collapse multiple spaces
        if (str_contains($title, '~')) {
            $title = str_replace('~', ' ', $title);
            $title = preg_replace('/\s{2,}/', ' ', $title);
            $title = trim($title);
        }

        // En dash or em dash as separator: "Title – Subtitle" → "Title: Subtitle"
        if (preg_match('/\s+[–—]\s+/u', $title) && substr_count($title, ': ') === 0) {
            $title = preg_replace('/\s+[–—]\s+/u', ': ', $title);
        }

        // Single hyphen as separator: "Title - Subtitle" → "Title: Subtitle"
        // Only when surrounded by spaces and not part of a compound word or multiple hyphens
        if (substr_count($title, ' - ') === 1 && substr_count($title, ': ') === 0) {
            $title = preg_replace('/\s+-\s+/', ': ', $title);
        }

        // Fix individual word capitalisation for mixed-case titles that didn't trigger ALL CAPS
        if (!$wasAllCaps) {
            $title = $this->fixWordCapitalization($title);
        }

        // Strip whitespace before a colon: "Title : Subtitle" → "Title: Subtitle"
        $title = preg_replace('/\s+:/', ':', $title);

        // Ensure space after a colon when followed directly by a letter: "Title:Subtitle" → "Title: Subtitle"
        $title = preg_replace('/:([\p{L}])/u', ': $1', $title);

        // Minor words lowercase (not at start, not after a colon)
        $title = $this->lowercaseMinorWords($title);

        return trim($title);
    }

    private function isAllCaps(string $title): bool
    {
        // Split into words, check if significantly all-caps
        $words = preg_split('/\s+/', $title);
        $multiLetterWords = array_filter($words, fn($w) => strlen(preg_replace('/[^a-zA-Z]/', '', $w)) > 2);

        if (count($multiLetterWords) === 0) return false;

        $capsCount = 0;
        foreach ($multiLetterWords as $word) {
            $letters = preg_replace('/[^a-zA-Z]/', '', $word);
            if ($letters === strtoupper($letters)) {
                $capsCount++;
            }
        }

        // All-caps if 75%+ of multi-letter words are uppercase
        return ($capsCount / count($multiLetterWords)) >= 0.75;
    }

    private function toTitleCase(string $title): string
    {
        $words = explode(' ', $title);
        $result = [];
        foreach ($words as $word) {
            $letters = preg_replace('/[^a-zA-Z]/', '', $word);
            if ($letters === '' || $letters !== strtoupper($letters)) {
                // Mixed case or no letters — keep as-is
                $result[] = $word;
            } elseif (in_array($letters, self::KNOWN_CAPS)) {
                // Known abbreviation — keep uppercase
                $result[] = $word;
            } elseif ($this->isRomanNumeral($letters)) {
                // Roman numeral (I, II, III, IV, VI, VII, VIII, IX, X, etc.) — keep uppercase
                $result[] = $word;
            } elseif (strlen($letters) <= 3) {
                // Short (1-3 letters): use vowels to distinguish real words from acronyms
                // Minor words (the, is, of...) → title-case; lowercaseMinorWords handles placement
                // No vowels (ZPF, MSX...) → likely an acronym, keep as-is
                // Has vowels (ALL, YOU...) → real word, title-case it
                if (in_array(strtolower($letters), self::MINOR_WORDS)) {
                    $result[] = ucfirst(strtolower($word));
                } elseif (!preg_match('/[AEIOU]/i', $letters)) {
                    $result[] = $word;
                } else {
                    $result[] = ucfirst(strtolower($word));
                }
            } else {
                $result[] = ucfirst(strtolower($word));
            }
        }
        return implode(' ', $result);
    }

    private function isRomanNumeral(string $str): bool
    {
        return (bool) preg_match('/^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/i', $str)
            && strlen($str) > 0;
    }

    /**
     * Fix individual words in a mixed-case title:
     * - ALL CAPS words with 5+ letters → Title Case (AVILION → Avilion)
     * - all-lowercase non-minor, non-first words → Capitalise (forever → Forever)
     * Short ALL CAPS abbreviations (JRPG, etc.) are left untouched.
     */
    private function fixWordCapitalization(string $title): string
    {
        $words = explode(' ', $title);
        $result = [];
        $isFirst = true;

        foreach ($words as $word) {
            $letters = preg_replace('/[^a-zA-Z]/', '', $word);

            if ($letters === '') {
                $result[] = $word;
                continue;
            }

            // ALL CAPS word with 5+ letters → Title Case (unless a known abbreviation or Roman numeral)
            if ($letters === strtoupper($letters) && strlen($letters) >= 5
                && !$this->isRomanNumeral($letters)
                && !in_array($letters, self::KNOWN_CAPS)
            ) {
                $result[] = ucfirst(strtolower($word));
            }
            // All-lowercase non-minor, non-first word → Capitalise
            elseif (!$isFirst && $letters === strtolower($letters) && !in_array(strtolower($letters), self::MINOR_WORDS)) {
                $result[] = ucfirst($word);
            }
            else {
                $result[] = $word;
            }

            $isFirst = false;
        }

        return implode(' ', $result);
    }

    private function lowercaseMinorWords(string $title): string
    {
        // Split on spaces, lowercase minor words unless they start the title or follow ": "
        $words = explode(' ', $title);
        $result = [];
        $forceCapNext = true; // first word always capitalised

        foreach ($words as $word) {
            if ($forceCapNext) {
                $result[] = $word;
                $forceCapNext = false;
            } elseif (in_array(strtolower($word), self::MINOR_WORDS)) {
                $result[] = strtolower($word);
            } else {
                $result[] = $word;
            }

            // After a colon, next word should be capitalised
            if (str_ends_with(rtrim($word, ' '), ':')) {
                $forceCapNext = true;
            }
        }

        return implode(' ', $result);
    }
}
