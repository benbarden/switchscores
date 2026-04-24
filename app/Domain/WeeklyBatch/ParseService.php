<?php

namespace App\Domain\WeeklyBatch;

use Carbon\Carbon;
use App\Models\Console;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Domain\WeeklyBatchRawPage\Repository as WeeklyBatchRawPageRepository;

class ParseService
{
    // Collection prefix → link_title mapping
    private const COLLECTION_PREFIXES = [
        'ACA NeoGeo'                => 'aca-neogeo',
        'Arcade Archives 2'         => 'arcade-archives-2',
        'Arcade Archives'           => 'arcade-archives',
        'Console Archives'          => 'console-archives',
        'Egg Console'               => 'egg-console',
        'Pixel Game Maker Series'   => 'pixel-game-maker',
    ];

    // Title patterns that auto-mark an item as a bundle at parse time
    private const BUNDLE_PATTERNS = [
        '/\bbundle\b/i',
    ];

    // Title/price signals suggesting a game may be low quality (flagged for review, not auto-marked)
    private const LQ_TITLE_PATTERNS = [
        '/\bSIMULATOR\b/i',
        '/^[A-Z\d:!.]+(\s[A-Z\s\d:!.]+)+$/',   // ALL CAPS multi-word titles
        '/\bHENTAI\b/i',
    ];

    public function __construct(
        private RawTextParser $parser,
        private TitleNormaliser $titleNormaliser,
        private GameRepository $repoGame,
        private WeeklyBatchItemRepository $repoItem,
        private WeeklyBatchRawPageRepository $repoRawPage
    ) {
    }

    /**
     * Parse a raw page, filter by date range, check for DB duplicates,
     * and save items to weekly_batch_items.
     *
     * Returns a summary array for display.
     */
    public function parsePage(int $batchId, string $console, string $listType, int $pageNumber, string $batchDate): array
    {
        $rawPage = $this->repoRawPage->find($batchId, $console, $listType, $pageNumber);
        if (!$rawPage) {
            return ['error' => 'Raw page not found'];
        }

        [$dateFrom, $dateTo] = $this->getDateRange($batchDate, $listType);
        $consoleId = $console === 'switch-2' ? Console::ID_SWITCH_2 : Console::ID_SWITCH_1;

        // Snapshot existing items before deleting so we can restore URLs and statuses
        $snapshot = $this->repoItem->getForListPage($batchId, $console, $listType, $pageNumber)
            ->keyBy('title_raw');

        // Delete any previously parsed items for this page (re-parse replaces them)
        $this->repoItem->deleteForListPage($batchId, $console, $listType, $pageNumber);

        $rawEntries  = $this->parser->parse($rawPage->raw_content);
        $summary     = ['in_range' => 0, 'out_of_range' => 0, 'already_in_db' => 0, 'total_parsed' => count($rawEntries)];

        foreach ($rawEntries as $sortOrder => $entry) {
            $titleRaw        = $entry['title_raw'];
            $title           = $this->titleNormaliser->normalise($titleRaw);
            $releaseDate     = $entry['release_date'];

            // Date range filter
            if (!$releaseDate || $releaseDate < $dateFrom || $releaseDate > $dateTo) {
                $this->repoItem->create([
                    'batch_id'    => $batchId,
                    'console'     => $console,
                    'list_type'   => $listType,
                    'page_number' => $pageNumber,
                    'sort_order'  => $sortOrder,
                    'title'       => $title,
                    'title_raw'   => $titleRaw,
                    'release_date' => $releaseDate,
                    'price_gbp'   => $entry['price_gbp'],
                    'price_raw'   => $entry['price_raw'],
                    'price_flag'  => $entry['price_flag'] ? 1 : 0,
                    'price_flag_reason' => $entry['price_flag_reason'],
                    'nintendo_genres'   => $entry['nintendo_genres'],
                    'description'       => $entry['description'],
                    'item_status' => 'out_of_range',
                ]);
                $summary['out_of_range']++;
                continue;
            }

            // DB duplicate check
            $existingGame = $this->repoGame->getByTitleAndConsole($title, $consoleId);
            if ($existingGame) {
                $this->repoItem->create([
                    'batch_id'    => $batchId,
                    'console'     => $console,
                    'list_type'   => $listType,
                    'page_number' => $pageNumber,
                    'sort_order'  => $sortOrder,
                    'title'       => $title,
                    'title_raw'   => $titleRaw,
                    'release_date' => $releaseDate,
                    'price_gbp'   => $entry['price_gbp'],
                    'price_raw'   => $entry['price_raw'],
                    'price_flag'  => $entry['price_flag'] ? 1 : 0,
                    'price_flag_reason' => $entry['price_flag_reason'],
                    'nintendo_genres'   => $entry['nintendo_genres'],
                    'description'       => $entry['description'],
                    'item_status' => 'already_in_db',
                    'game_id'     => $existingGame->id,
                ]);
                $summary['already_in_db']++;
                continue;
            }

            // Active item — detect collection, bundles, and LQ signals
            $collection   = $this->matchCollection($title);
            $isBundle     = $this->detectBundle($title);
            $lqFlagReason = $this->detectLqSignals($title, $entry['price_gbp']);

            $item = $this->repoItem->create([
                'batch_id'    => $batchId,
                'console'     => $console,
                'list_type'   => $listType,
                'page_number' => $pageNumber,
                'sort_order'  => $sortOrder,
                'title'       => $title,
                'title_raw'   => $titleRaw,
                'release_date' => $releaseDate,
                'price_gbp'   => $entry['price_gbp'],
                'price_raw'   => $entry['price_raw'],
                'price_flag'  => $entry['price_flag'] ? 1 : 0,
                'price_flag_reason' => $entry['price_flag_reason'],
                'nintendo_genres'   => $entry['nintendo_genres'],
                'description'       => $entry['description'],
                'collection'  => $collection,
                'item_status' => $isBundle ? 'bundle' : 'pending',
                'lq_flag'     => $lqFlagReason ? 1 : 0,
                'lq_flag_reason' => $lqFlagReason,
            ]);

            if ($snapshot->has($titleRaw)) {
                $this->repoItem->restoreSnapshot($item, $snapshot->get($titleRaw));
            }

            $summary['in_range']++;
        }

        $this->repoRawPage->markParsed($rawPage);

        return $summary;
    }

    /**
     * Returns [dateFrom, dateTo] as Y-m-d strings based on batch date and list type.
     * New:      previous Saturday → batch date (Friday)
     * Upcoming: batch date (Friday) → following Sunday
     */
    public function getDateRange(string $batchDate, string $listType): array
    {
        $friday = Carbon::parse($batchDate);

        if ($listType === 'new') {
            $from = $friday->copy()->subDays(6)->toDateString(); // previous Saturday
            $to   = $friday->toDateString();
        } else {
            $from = $friday->toDateString();
            $to   = $friday->copy()->addDays(9)->toDateString(); // following Sunday
        }

        return [$from, $to];
    }

    /**
     * Re-run normalisation on a single item's title_raw and update its derived fields.
     * Preserves existing status, URLs, and fetched data.
     */
    public function reparseItem(\App\Models\WeeklyBatchItem $item): void
    {
        $title      = $this->titleNormaliser->normalise($item->title_raw);
        $collection = $this->matchCollection($title);
        $isBundle   = $this->detectBundle($title);
        $lqReason   = $this->detectLqSignals($title, $item->price_gbp);

        $item->title          = $title;
        $item->collection     = $collection;
        $item->item_status    = $isBundle ? 'bundle' : $item->item_status;
        $item->lq_flag        = $lqReason ? 1 : 0;
        $item->lq_flag_reason = $lqReason;
        $item->save();
    }

    private function matchCollection(string $title): ?string
    {
        foreach (self::COLLECTION_PREFIXES as $prefix => $linkTitle) {
            if (str_starts_with($title, $prefix)) {
                return $linkTitle;
            }
        }
        return null;
    }

    private function detectBundle(string $title): bool
    {
        foreach (self::BUNDLE_PATTERNS as $pattern) {
            if (preg_match($pattern, $title)) {
                return true;
            }
        }
        return false;
    }

    private function detectLqSignals(string $title, ?float $price): ?string
    {
        foreach (self::LQ_TITLE_PATTERNS as $pattern) {
            if (preg_match($pattern, $title)) {
                return 'Title pattern: '.$title;
            }
        }
        if ($price !== null && $price > 0 && $price <= 0.99) {
            return 'Very low price: £'.number_format($price, 2);
        }
        return null;
    }
}
