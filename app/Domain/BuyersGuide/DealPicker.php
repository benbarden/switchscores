<?php

namespace App\Domain\BuyersGuide;

/**
 * Picks the buyer's guide items that are on offer at Amazon US, for the Deals section.
 *
 * A deal is a saving Amazon itself reports against a basis listed in config (list price, or
 * the recent "was" price), of at least the minimum percentage. A listing that violates MAP
 * has no price to show, so it's never a deal.
 */
class DealPicker
{
    // How each saving basis is labelled next to the old price
    const BASIS_LABELS = [
        'LIST_PRICE' => 'List price',
        'WAS_PRICE' => 'Was',
    ];

    /**
     * @param array $candidates each with asin, title and url (the guide's own link)
     * @param array $offers the OfferStore items, keyed by ASIN
     * @return array the deals, biggest saving first, each with title, url, imageUrl, price,
     *               wasPrice, wasLabel and savingsPercent
     */
    public function pick(array $candidates, array $offers): array
    {
        $deals = [];
        $seen = [];
        foreach ($candidates as $candidate) {
            $asin = $candidate['asin'] ?? null;
            if (!$asin || isset($seen[$asin]) || !isset($offers[$asin])) {
                continue;
            }
            $offer = $offers[$asin];
            if (!$this->isDeal($offer)) {
                continue;
            }
            $seen[$asin] = true;
            $deals[] = [
                'title' => $candidate['title'],
                'url' => $candidate['url'],
                'imageUrl' => $offer['imageUrl'] ?? null,
                'price' => $offer['price'],
                'wasPrice' => $offer['wasPrice'],
                'wasLabel' => self::BASIS_LABELS[$offer['savingBasisType']] ?? 'Was',
                'savingsPercent' => $offer['savingsPercent'],
            ];
        }

        // Stable for equal savings, so the guide's own order breaks ties
        usort($deals, fn($a, $b) => $b['savingsPercent'] <=> $a['savingsPercent']);

        return $deals;
    }

    public function isDeal(array $offer): bool
    {
        if (!empty($offer['violatesMap']) || empty($offer['price']) || empty($offer['wasPrice'])) {
            return false;
        }

        $types = config('buyers-guide.deals.saving_types', []);
        if (!in_array($offer['savingBasisType'] ?? null, $types, true)) {
            return false;
        }

        return ($offer['savingsPercent'] ?? 0) >= (int) config('buyers-guide.deals.min_percent', 10);
    }
}
