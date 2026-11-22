<?php

namespace App\Domain\AffiliateCodes;

use App\Domain\Game\Repository\AmazonLinkCheckRepository;
use App\Enums\AmazonLinkCheckResult;

/**
 * The Amazon US link check's problems, in the groups staff work through, for the list page, the
 * Affiliates dashboard and the staff home tile. A Check by hand link marked as fine is set aside
 * and not counted.
 */
class UsLinkCheckSummary
{
    const GROUPS = [
        AmazonLinkCheckResult::GROUP_BROKEN => [
            'label' => 'Broken',
            'help' => 'The product wasn\'t found, or the link is for the other console. Find the right product, or set the game to No product.',
        ],
        AmazonLinkCheckResult::GROUP_CANT_BUY => [
            'label' => 'Can\'t buy',
            'help' => 'Out of stock, or nobody selling it. Look for another listing of the same game that\'s in stock. If there isn\'t one, leave it: some come back.',
        ],
        AmazonLinkCheckResult::GROUP_CHECK_BY_HAND => [
            'label' => 'Check by hand',
            'help' => 'Often fine. "API can\'t see it" usually means the page still works. A region edition can be the best listing there is. Open the link, and mark it as fine if it is.',
        ],
    ];

    public function __construct(
        private AmazonLinkCheckRepository $repoLinkCheck,
        private UsLinkCheckTitles $titles,
        private Amazon $amazon,
    )
    {
    }

    /**
     * @return array{groups: array<string, array>, markedOk: array, counts: array<string, int>, total: int, lastCheckedAt: ?string}
     *   each row: game, result (AmazonLinkCheckResult), amazonTitle, amazonUrl (untagged), searchUrl
     */
    public function build(): array
    {
        $titles = $this->titles->all();
        $groups = array_fill_keys(array_keys(self::GROUPS), []);
        $markedOk = [];

        foreach ($this->repoLinkCheck->usProblems() as $game) {
            $result = $game->amazon_us_check_result;
            $group = $result?->group();
            if ($group === null) {
                continue;
            }

            $row = [
                'game' => $game,
                'result' => $result,
                'amazonTitle' => $titles[$game->amazon_us_asin] ?? null,
                // Untagged, so staff checking a product don't click through the affiliate tag
                'amazonUrl' => 'https://www.amazon.com/dp/'.rawurlencode($game->amazon_us_asin),
                'searchUrl' => $this->amazon->staffSearchUrl($game, 'us'),
            ];

            if ($group === AmazonLinkCheckResult::GROUP_CHECK_BY_HAND && $game->amazon_us_check_ok_asin === $game->amazon_us_asin) {
                $markedOk[] = $row;
            } else {
                $groups[$group][] = $row;
            }
        }

        $counts = array_map('count', $groups);

        return [
            'groups' => $groups,
            'markedOk' => $markedOk,
            'counts' => $counts,
            'total' => array_sum($counts),
            'lastCheckedAt' => $this->repoLinkCheck->lastUsCheckedAt(),
        ];
    }
}
