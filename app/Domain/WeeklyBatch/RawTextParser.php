<?php

namespace App\Domain\WeeklyBatch;

/**
 * Parses raw Nintendo listing text into structured game entries.
 *
 * Expected input format per game:
 *   Title
 *   Title
 *
 *   Nintendo Switch [2] • DD/MM/YYYY • Genre1, Genre2
 *
 *   [Starting from: ]£X.XX[£Y.YY]*
 *
 *   Description text
 *
 * Special lines that are skipped: "PAGE N", "Demo available", header lines.
 */
class RawTextParser
{
    // Lines to strip before processing
    private const SKIP_LINE_PATTERNS = [
        '/^PAGE\s+\d+$/i',
        '/^Demo available$/i',
        '/^Switch \d (New|Upcoming)/i',
    ];

    // Meta line pattern: "Nintendo Switch [2] • DD/MM/YYYY • Genres"
    // Uses \S for the separator (bullet char) to avoid UTF-8 encoding issues with literal • in const strings
    private const META_PATTERN = '/^Nintendo Switch(?:\s+2)?\s+\S\s+(\d{2}\/\d{2}\/\d{4})\s+\S\s+(.+)$/u';

    // Price patterns
    private const PRICE_PATTERN = '/^(?:Starting from:\s*)?£([\d.]+)/';

    public function parse(string $rawContent): array
    {
        $lines = explode("\n", str_replace("\r\n", "\n", $rawContent));
        $lines = array_map('trim', $lines);

        // Remove skip lines
        $lines = array_filter($lines, fn($line) => !$this->shouldSkipLine($line));
        $lines = array_values($lines);

        $entries = [];
        $i = 0;
        $total = count($lines);

        while ($i < $total) {
            // Look for a meta line
            if (preg_match(self::META_PATTERN, $lines[$i], $metaMatches)) {
                // Meta line found at $i. Title is the nearest non-empty line(s) before it.
                $title = $this->findTitleBefore($lines, $i);
                $dateRaw  = $metaMatches[1]; // DD/MM/YYYY
                $genresRaw = trim($metaMatches[2]);

                // Skip blank lines after meta, then get price
                $j = $i + 1;
                while ($j < $total && $lines[$j] === '') $j++;
                $priceRaw = ($j < $total) ? $lines[$j] : '';
                $j++;

                // Skip blank lines, then collect description
                while ($j < $total && $lines[$j] === '') $j++;
                $descLines = [];
                while ($j < $total && $lines[$j] !== '') {
                    // Stop if we hit another meta line or a new title block
                    if (preg_match(self::META_PATTERN, $lines[$j])) break;
                    $descLines[] = $lines[$j];
                    $j++;
                }
                $description = implode(' ', $descLines);

                if ($title !== '') {
                    $entries[] = $this->buildEntry($title, $dateRaw, $genresRaw, $priceRaw, $description);
                }

                $i = $j;
            } else {
                $i++;
            }
        }

        return $entries;
    }

    private function findTitleBefore(array $lines, int $metaIndex): string
    {
        // Walk backwards from meta line, skip blanks, take the first non-empty line
        $idx = $metaIndex - 1;
        while ($idx >= 0 && $lines[$idx] === '') {
            $idx--;
        }
        if ($idx < 0) return '';

        // The title is repeated twice; take just the one nearest to the meta line
        return $lines[$idx];
    }

    private function buildEntry(string $titleRaw, string $dateRaw, string $genresRaw, string $priceRaw, string $description): array
    {
        [$releaseDate, $dateParsed] = $this->parseDate($dateRaw);
        [$priceGbp, $priceFlag, $priceFlagReason] = $this->parsePrice($priceRaw);

        return [
            'title_raw'         => $titleRaw,
            'release_date_raw'  => $dateRaw,
            'release_date'      => $releaseDate,    // Y-m-d string or null
            'price_raw'         => $priceRaw,
            'price_gbp'         => $priceGbp,
            'price_flag'        => $priceFlag,
            'price_flag_reason' => $priceFlagReason,
            'nintendo_genres'   => $genresRaw,
            'description'       => $description,
        ];
    }

    private function parseDate(string $dateRaw): array
    {
        // DD/MM/YYYY → Y-m-d
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateRaw, $m)) {
            $date = $m[3].'-'.$m[2].'-'.$m[1];
            return [$date, true];
        }
        return [null, false];
    }

    private function parsePrice(string $priceRaw): array
    {
        if ($priceRaw === '') {
            return [null, true, 'price missing'];
        }

        $isStartingFrom = str_starts_with(strtolower($priceRaw), 'starting from');
        $hasSalePrice   = (bool) preg_match('/£[\d.]+£[\d.]+/', $priceRaw);

        if (!preg_match(self::PRICE_PATTERN, $priceRaw, $m)) {
            return [null, true, 'price not parseable: '.$priceRaw];
        }

        $price = (float) $m[1];
        $flag  = false;
        $reason = '';

        if ($price === 0.0) {
            $flag   = true;
            $reason = '£0.00 — confirm free-to-play or check game page';
        } elseif ($isStartingFrom) {
            $flag   = true;
            $reason = 'Starting from price — validate on game page';
        } elseif ($hasSalePrice) {
            // Original price is always first — extracted correctly, no review needed
        }

        return [$price, $flag, $reason];
    }

    private function shouldSkipLine(string $line): bool
    {
        if ($line === '') return false; // blanks are significant
        foreach (self::SKIP_LINE_PATTERNS as $pattern) {
            if (preg_match($pattern, $line)) return true;
        }
        return false;
    }
}
