<?php

namespace App\Domain\WeeklyBatch;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Parses Nintendo store search-result HTML (copied from the browser) into structured
 * game entries.
 *
 * Richer than {@see RawTextParser}: every game is a self-contained
 * <li data-nsuid="..."> row, so game boundaries are unambiguous and we can capture
 * data the plain-text view throws away — the Nintendo store URL, the square packshot
 * URL, and Nintendo's stable NSUID.
 *
 * Output entries use the same keys as RawTextParser (title_raw, release_date,
 * price_gbp, etc.) plus: nsuid, nintendo_url, packshot_url, has_demo,
 * is_download_only, console_raw.
 */
class HtmlListParser
{
    public function parse(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $crawler = new Crawler($html);
        $rows    = $crawler->filter('li[data-nsuid]');

        $entries = [];
        foreach ($rows as $node) {
            $entry = $this->buildEntry(new Crawler($node));
            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * Independent count of game rows present in the HTML, used as a safety check:
     * if this exceeds the number of parsed entries, some rows failed to parse.
     */
    public function countGameBlocks(string $html): int
    {
        if (trim($html) === '') {
            return 0;
        }

        return (new Crawler($html))->filter('li[data-nsuid]')->count();
    }

    /**
     * Quick sniff: does this content look like Nintendo store HTML rather than
     * the plain-text listing?
     */
    public function looksLikeHtml(string $content): bool
    {
        return str_contains($content, '<li') && str_contains($content, 'data-nsuid');
    }

    private function buildEntry(Crawler $li): ?array
    {
        $titleRaw = $this->attr($li, 'data-nt-item-title-master')
            ?: $this->attr($li, 'data-nt-item-title');

        // A row with no title is unusable — let the count check flag it.
        if ($titleRaw === '') {
            return null;
        }

        [$consoleRaw, $dateRaw, $genresRaw] = $this->parseMeta($li);
        [$priceRaw, $priceGbp, $priceFlag, $priceFlagReason] = $this->parsePrice($li);

        return [
            'nsuid'             => $this->attr($li, 'data-nsuid'),
            'title_raw'         => $titleRaw,
            'nintendo_url'      => $this->firstAttr($li, 'a[href]', 'href'),
            'packshot_url'      => $this->firstImageUrl($li),
            'console_raw'       => $consoleRaw,
            'release_date_raw'  => $dateRaw,
            'release_date'      => $this->toIsoDate($dateRaw),
            'nintendo_genres'   => $genresRaw,
            'price_raw'         => $priceRaw,
            'price_gbp'         => $priceGbp,
            'price_flag'        => $priceFlag,
            'price_flag_reason' => $priceFlagReason,
            'description'       => $this->firstText($li, 'p.visible-lg'),
            'has_demo'          => $li->filter('.plm-priority-labels__label--demo')->count() > 0,
            'is_download_only'  => $li->filter('[data-component="red-cap"]')->count() > 0,
        ];
    }

    /**
     * Reads "Nintendo Switch 2 • 16/07/2026 • Adventure, Other, Puzzle" from the
     * .page-data block. Returns [consoleRaw, dateRaw, genresRaw]; genres empty when absent.
     */
    private function parseMeta(Crawler $li): array
    {
        $pageData = $li->filter('.page-data');
        if ($pageData->count() === 0) {
            return ['', '', ''];
        }
        $pageData = $pageData->first();
        $pdText   = $this->normalise($pageData->text());

        // Console is everything before the first bullet. Read it from the text rather
        // than the first <span>, so dual-console rows ("Nintendo Switch, Nintendo
        // Switch 2") — where the name is split across spans — come through whole.
        $consoleRaw = trim(explode('•', $pdText)[0]);

        $dateRaw = '';
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $pdText, $m)) {
            $dateRaw = $m[1];
        }

        // Genres live in the desktop-only span; strip the leading bullet/whitespace.
        $genresRaw = '';
        $genreSpan = $pageData->filter('.hidden-xs.hidden-sm');
        if ($genreSpan->count() > 0) {
            $genresRaw = preg_replace('/^[\s•·]+/u', '', $this->normalise($genreSpan->text()));
        }

        return [$consoleRaw, $dateRaw, trim($genresRaw)];
    }

    /**
     * Reads price from .price-small. Handles three shapes:
     *   - discount:      <span class="original-price">£17.75</span><span class="discount">£15.97*</span>
     *   - starting from: <span data-price-from-label>Starting from:</span><span>£0.00*</span>
     *   - plain:         <span>£8.99*</span>
     *
     * price_gbp is the first (headline/original) price, matching RawTextParser.
     * Flag semantics mirror RawTextParser::parsePrice.
     */
    private function parsePrice(Crawler $li): array
    {
        $priceSmall = $li->filter('.price-small');
        if ($priceSmall->count() === 0) {
            return ['', null, true, 'price missing'];
        }
        $priceSmall = $priceSmall->first();

        $priceRaw     = $this->normalise($priceSmall->text());
        $startingFrom = $priceSmall->filter('[data-price-from-label]')->count() > 0;
        $hasDiscount  = $priceSmall->filter('.discount')->count() > 0;

        if (!preg_match('/£([\d.]+)/', $priceRaw, $m)) {
            return [$priceRaw, null, true, 'price not parseable: '.$priceRaw];
        }

        $price  = (float) $m[1];
        $flag   = false;
        $reason = '';

        if ($price === 0.0) {
            $flag   = true;
            $reason = '£0.00 — confirm free-to-play or check game page';
        } elseif ($startingFrom) {
            $flag   = true;
            $reason = 'Starting from price — validate on game page';
        } elseif ($hasDiscount) {
            // Original price is first — captured correctly, no review needed.
        }

        return [$priceRaw, $price, $flag, $reason];
    }

    private function firstImageUrl(Crawler $li): string
    {
        $img = $li->filter('img');
        if ($img->count() === 0) {
            return '';
        }
        $img = $img->first();

        // Prefer the eager src; fall back to lazy-load attributes if a page uses them.
        return $img->attr('src') ?? $img->attr('data-src') ?? '';
    }

    private function toIsoDate(string $dateRaw): ?string
    {
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dateRaw, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1];
        }
        return null;
    }

    private function attr(Crawler $node, string $attr): string
    {
        return trim((string) $node->attr($attr));
    }

    private function firstAttr(Crawler $node, string $selector, string $attr): string
    {
        $found = $node->filter($selector);
        return $found->count() > 0 ? trim((string) $found->first()->attr($attr)) : '';
    }

    private function firstText(Crawler $node, string $selector): string
    {
        $found = $node->filter($selector);
        return $found->count() > 0 ? $this->normalise($found->first()->text()) : '';
    }

    private function normalise(string $text): string
    {
        // Collapse whitespace (including the non-breaking spaces Nintendo peppers in).
        return trim(preg_replace('/\s+/u', ' ', str_replace("\u{00A0}", ' ', $text)));
    }
}
