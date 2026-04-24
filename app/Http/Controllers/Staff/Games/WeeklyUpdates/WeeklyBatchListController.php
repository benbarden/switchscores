<?php

namespace App\Http\Controllers\Staff\Games\WeeklyUpdates;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\WeeklyBatch\Repository as WeeklyBatchRepository;
use App\Domain\WeeklyBatch\CategorySuggester;
use App\Domain\WeeklyBatch\ParseService;
use App\Domain\WeeklyBatch\GameImporter;
use App\Domain\WeeklyBatch\NintendoPageFetcher;
use App\Domain\WeeklyBatchItem\Repository as WeeklyBatchItemRepository;
use App\Domain\WeeklyBatchRawPage\Repository as WeeklyBatchRawPageRepository;
use App\Models\WeeklyBatchItem;

class WeeklyBatchListController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private WeeklyBatchRepository $repoBatch,
        private WeeklyBatchItemRepository $repoItem,
        private WeeklyBatchRawPageRepository $repoRawPage,
        private ParseService $parseService,
        private NintendoPageFetcher $fetcher,
        private CategorySuggester $categorySuggester,
        private CategoryRepository $repoCategory,
        private GamesCompanyRepository $repoGamesCompany,
        private GameRepository $repoGame,
        private GameImporter $gameImporter
    ) {
    }

    private function getBatch(int $batchId)
    {
        $batch = $this->repoBatch->find($batchId);
        if (!$batch) abort(404);
        return $batch;
    }

    private function listLabel(string $console, string $listType): string
    {
        $consoleLabel = $console === 'switch-2' ? 'Switch 2' : 'Switch 1';
        $typeLabel    = $listType === 'new' ? 'New' : 'Upcoming';
        return "{$consoleLabel} {$typeLabel}";
    }

    private function getStages(int $batchId, string $console, string $listType, int $rawPageCount, array $counts): array
    {
        $params = compact('batchId', 'console', 'listType');

        $fetchReached = ($counts['fetch_pending'] ?? 0) + ($counts['lq_review'] ?? 0)
                      + ($counts['packshot_pending'] ?? 0) + ($counts['category_pending'] ?? 0)
                      + ($counts['ready'] ?? 0) + ($counts['imported'] ?? 0) > 0;

        $pricesReached = ($counts['packshot_pending'] ?? 0) + ($counts['category_pending'] ?? 0)
                       + ($counts['ready'] ?? 0) + ($counts['imported'] ?? 0) > 0;

        $packshotsReached = $pricesReached;

        $categoriesReached = ($counts['category_pending'] ?? 0) + ($counts['ready'] ?? 0)
                           + ($counts['imported'] ?? 0) > 0;

        $confirmReached = ($counts['ready'] ?? 0) + ($counts['imported'] ?? 0) > 0;

        return [
            ['key' => 'raw',        'label' => 'Raw input',           'url' => route('staff.games.weekly-updates.list.raw',        $params), 'reachable' => true],
            ['key' => 'urls',       'label' => 'URL collection',      'url' => route('staff.games.weekly-updates.list.urls',       $params), 'reachable' => $rawPageCount > 0],
            ['key' => 'fetch',      'label' => 'Fetch and LQ review', 'url' => route('staff.games.weekly-updates.list.fetch',      $params), 'reachable' => $fetchReached],
            ['key' => 'publishers', 'label' => 'Publishers',          'url' => route('staff.games.weekly-updates.list.publishers', $params), 'reachable' => $pricesReached],
            ['key' => 'prices',     'label' => 'Prices',              'url' => route('staff.games.weekly-updates.list.prices',     $params), 'reachable' => $pricesReached],
            ['key' => 'packshots',  'label' => 'Packshots',           'url' => route('staff.games.weekly-updates.list.packshots',  $params), 'reachable' => $packshotsReached],
            ['key' => 'categories', 'label' => 'Categories',          'url' => route('staff.games.weekly-updates.list.categories', $params), 'reachable' => $categoriesReached],
            ['key' => 'confirm',    'label' => 'Confirm and import',  'url' => route('staff.games.weekly-updates.list.confirm',    $params), 'reachable' => $confirmReached],
        ];
    }

    // ---- Raw input ----

    public function raw($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Raw input';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $this->repoItem->clearSalePriceFlags($batchId, $console, $listType);

        $rawPages = $this->repoRawPage->getForList($batchId, $console, $listType);
        $items    = $this->repoItem->getForList($batchId, $console, $listType);
        $counts   = $this->repoItem->getStatusCounts($batchId, $console, $listType);

        [$dateFrom, $dateTo] = $this->parseService->getDateRange($batch->batch_date->toDateString(), $listType);

        // Per-page counts for the pages table
        $skippedStatuses  = [WeeklyBatchItem::STATUS_ALREADY_IN_DB, WeeklyBatchItem::STATUS_OUT_OF_RANGE];
        $pageCounts = [];
        foreach ($items->groupBy('page_number') as $pageNum => $pageItems) {
            $pageCounts[$pageNum] = [
                'in_range'      => $pageItems->whereNotIn('item_status', $skippedStatuses)->count(),
                'imported'      => $pageItems->where('item_status', WeeklyBatchItem::STATUS_IMPORTED)->count(),
                'already_in_db' => $pageItems->where('item_status', WeeklyBatchItem::STATUS_ALREADY_IN_DB)->count(),
                'out_of_range'  => $pageItems->where('item_status', WeeklyBatchItem::STATUS_OUT_OF_RANGE)->count(),
            ];
        }

        $bindings['Batch']          = $batch;
        $bindings['Console']        = $console;
        $bindings['ListType']       = $listType;
        $bindings['Label']          = $label;
        $bindings['RawPages']       = $rawPages;
        $bindings['Items']          = $items;
        $bindings['Counts']         = $counts;
        $bindings['PageCounts']     = $pageCounts;
        $bindings['NextPageNumber'] = $this->repoRawPage->getNextPageNumber($batchId, $console, $listType);
        $bindings['DateFrom']       = $dateFrom;
        $bindings['DateTo']         = $dateTo;
        $bindings['Stages']         = $this->getStages($batchId, $console, $listType, count($rawPages), $counts);
        $bindings['CurrentStage']   = 'raw';

        return view('staff.games.weekly-updates.list.raw', $bindings);
    }

    public function savePage($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);

        $rawContent = request('raw_content', '');
        $pageNumber = (int) request('page_number', 1);

        if (trim($rawContent) === '') {
            return redirect()->route('staff.games.weekly-updates.list.raw', compact('batchId', 'console', 'listType'))
                ->with('error', 'Raw content cannot be empty.');
        }

        $existingPage = $this->repoRawPage->find($batchId, $console, $listType, $pageNumber);
        if ($existingPage && $this->repoItem->hasAdvancedItems($batchId, $console, $listType, $pageNumber)) {
            return redirect()->back()
                ->with('error', "Page {$pageNumber} cannot be replaced — some items have already been imported.");
        }

        $this->repoRawPage->saveOrReplace($batchId, $console, $listType, $pageNumber, $rawContent);

        $summary = $this->parseService->parsePage(
            $batchId, $console, $listType, $pageNumber,
            $batch->batch_date->toDateString()
        );

        $message = "Page {$pageNumber} saved and parsed: {$summary['in_range']} in range, {$summary['already_in_db']} already in DB, {$summary['out_of_range']} out of range.";

        return redirect()->route('staff.games.weekly-updates.list.raw', compact('batchId', 'console', 'listType'))
            ->with('success', $message);
    }

    public function removePage($batchId, $console, $listType)
    {
        $pageNumber = (int) request('page_number');

        if ($this->repoItem->hasAdvancedItems($batchId, $console, $listType, $pageNumber)) {
            return redirect()->back()
                ->with('error', "Page {$pageNumber} cannot be removed — some items have already been imported.");
        }

        $this->repoRawPage->remove($batchId, $console, $listType, $pageNumber);
        $this->repoItem->deleteForListPage($batchId, $console, $listType, $pageNumber);

        return redirect()->route('staff.games.weekly-updates.list.raw', compact('batchId', 'console', 'listType'))
            ->with('success', "Page {$pageNumber} removed.");
    }

    // ---- Publisher review ----

    public function publishers($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Publishers';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $rawPages = $this->repoRawPage->getForList($batchId, $console, $listType);
        $counts   = $this->repoItem->getStatusCounts($batchId, $console, $listType);

        $fetchedStatuses = [
            WeeklyBatchItem::STATUS_LQ_REVIEW,
            WeeklyBatchItem::STATUS_PACKSHOT_PENDING,
            WeeklyBatchItem::STATUS_CATEGORY_PENDING,
            WeeklyBatchItem::STATUS_READY,
        ];

        $items = $this->repoItem->getForList($batchId, $console, $listType)
            ->whereIn('item_status', $fetchedStatuses)
            ->filter(fn($i) => !empty($i->publisher_normalised));

        $publishers = [];
        foreach ($items as $item) {
            $name = $item->publisher_normalised;
            if (!isset($publishers[$name])) {
                $company = $this->repoGamesCompany->findByNameCaseInsensitive($name);
                $status  = $company === null ? 'new' : ($company->is_low_quality ? 'lq' : 'known');
                $publishers[$name] = [
                    'name'               => $name,
                    'item_count'         => 0,
                    'company'            => $company,
                    'status'             => $status,
                    'db_game_count'      => $company?->publisher_games_count ?? 0,
                    'sample_nintendo_url' => null,
                ];
            }
            $publishers[$name]['item_count']++;
            if (!$publishers[$name]['sample_nintendo_url'] && $item->nintendo_url) {
                $publishers[$name]['sample_nintendo_url'] = $item->nintendo_url;
            }
        }

        usort($publishers, function ($a, $b) {
            $order = ['new' => 0, 'lq' => 1, 'known' => 2];
            return ($order[$a['status']] ?? 3) <=> ($order[$b['status']] ?? 3);
        });

        $bindings['Batch']        = $batch;
        $bindings['Console']      = $console;
        $bindings['ListType']     = $listType;
        $bindings['Label']        = $label;
        $bindings['Publishers']   = array_values($publishers);
        $bindings['Counts']       = $counts;
        $bindings['RawPages']     = $rawPages;
        $bindings['Stages']       = $this->getStages($batchId, $console, $listType, count($rawPages), $counts);
        $bindings['CurrentStage'] = 'publishers';

        return view('staff.games.weekly-updates.list.publishers', $bindings);
    }

    public function createPublisher($batchId, $console, $listType, \Illuminate\Http\Request $request)
    {
        $this->getBatch($batchId);

        $oldName = trim($request->input('old_name', ''));
        $newName = trim($request->input('new_name', ''));
        $isLq    = $request->boolean('is_lq');

        if ($newName === '') abort(422);

        // Rename batch items if the name was changed
        if ($oldName !== '' && $oldName !== $newName) {
            $this->repoItem->renamePublisher($batchId, $console, $listType, $oldName, $newName);
        }

        // Check if the name already exists in the DB
        $existing = $this->repoGamesCompany->findByNameCaseInsensitive($newName);
        if ($existing) {
            return response()->json([
                'status'    => 'already_exists',
                'is_lq'     => (bool) $existing->is_low_quality,
                'name'      => $existing->name,
                'company_id' => $existing->id,
            ]);
        }

        $linkTitle = (new \App\Domain\Url\LinkTitle())->generate($newName);
        $company   = $this->repoGamesCompany->createCompany($newName, $linkTitle, $isLq);

        $itemCount = 0;
        if ($isLq) {
            $itemCount = $this->repoItem->markPublisherItemsLq($batchId, $console, $listType, $newName);
        }

        return response()->json([
            'status'     => 'created',
            'is_lq'      => $isLq,
            'name'       => $newName,
            'company_id' => $company->id,
            'item_count' => $itemCount,
        ]);
    }

    public function markPublisherLq($batchId, $console, $listType, \Illuminate\Http\Request $request)
    {
        $this->getBatch($batchId);

        $name = trim($request->input('name', ''));
        if ($name === '') abort(422);

        $count = $this->repoItem->markPublisherItemsLq($batchId, $console, $listType, $name);

        return response()->json(['status' => 'ok', 'count' => $count]);
    }

    public function prices($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Prices';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $counts    = $this->repoItem->getStatusCounts($batchId, $console, $listType);
        $rawPages  = $this->repoRawPage->getForList($batchId, $console, $listType);
        $allItems  = $this->repoItem->getActiveForList($batchId, $console, $listType);
        $flagged   = $allItems->filter(fn($i) => $i->price_flag);
        $unflagged = $allItems->filter(fn($i) => !$i->price_flag);

        $bindings['Batch']         = $batch;
        $bindings['Console']       = $console;
        $bindings['ListType']      = $listType;
        $bindings['Label']         = $label;
        $bindings['FlaggedItems']  = $flagged;
        $bindings['UnflaggedItems'] = $unflagged;
        $bindings['Counts']        = $counts;
        $bindings['Stages']        = $this->getStages($batchId, $console, $listType, count($rawPages), $counts);
        $bindings['CurrentStage']  = 'prices';

        return view('staff.games.weekly-updates.list.prices', $bindings);
    }

    public function savePrices($batchId, $console, $listType, \Illuminate\Http\Request $request)
    {
        $this->getBatch($batchId);

        $prices = $request->input('prices', []);
        $saved  = 0;

        foreach ($prices as $itemId => $priceInput) {
            $priceInput = trim($priceInput);
            if ($priceInput === '') continue;

            $price = (float) $priceInput;
            if ($price < 0) continue;

            $item = $this->repoItem->find((int) $itemId);
            if (!$item || $item->batch_id != $batchId) continue;

            $this->repoItem->updatePrice($item, $price);
            $saved++;
        }

        return redirect()->route('staff.games.weekly-updates.list.prices', compact('batchId', 'console', 'listType'))
            ->with('success', "{$saved} price".($saved !== 1 ? 's' : '')." saved.");
    }

    // ---- URL collection ----

    public function urls($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - URL collection';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $rawPages = $this->repoRawPage->getForList($batchId, $console, $listType);
        $allItems = $this->repoItem->getForList($batchId, $console, $listType);
        $counts   = $this->repoItem->getStatusCounts($batchId, $console, $listType);

        // Group items by page number for the tabbed UI
        $itemsByPage = $allItems->groupBy('page_number');

        $bindings['Batch']        = $batch;
        $bindings['Console']      = $console;
        $bindings['ListType']     = $listType;
        $bindings['Label']        = $label;
        $bindings['RawPages']     = $rawPages;
        $bindings['ItemsByPage']  = $itemsByPage;
        $bindings['Counts']       = $counts;
        $bindings['Stages']       = $this->getStages($batchId, $console, $listType, count($rawPages), $counts);
        $bindings['CurrentStage'] = 'urls';

        return view('staff.games.weekly-updates.list.urls', $bindings);
    }

    public function saveUrls($batchId, $console, $listType)
    {
        $this->getBatch($batchId);

        // urls[item_id] = url string
        $urls = request('urls', []);

        foreach ($urls as $itemId => $url) {
            $url = trim($url);

            $item = $this->repoItem->find((int) $itemId);
            if (!$item || $item->batch_id != $batchId) continue;

            if ($url === '') {
                if ($item->nintendo_url) {
                    $this->repoItem->clearUrl($item);
                }
            } else {
                $this->repoItem->updateUrl($item, $url);
            }
        }

        $pageNumber = request('page_number', 1);

        return redirect()->route('staff.games.weekly-updates.list.urls', compact('batchId', 'console', 'listType'))
            ->with('success', "URLs saved for page {$pageNumber}.");
    }

    // ---- Fetch & LQ review ----

    public function fetch($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Fetch and LQ review';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $rawPages    = $this->repoRawPage->getForList($batchId, $console, $listType);
        $counts      = $this->repoItem->getStatusCounts($batchId, $console, $listType);
        $allItems    = $this->repoItem->getForList($batchId, $console, $listType);
        $lqItems     = $this->repoItem->getByStatus($batchId, $console, $listType, WeeklyBatchItem::STATUS_LQ_REVIEW);
        $lqAutoItems = $this->repoItem->getByStatus($batchId, $console, $listType, WeeklyBatchItem::STATUS_LOW_QUALITY);
        $bundleItems = $this->repoItem->getByStatus($batchId, $console, $listType, WeeklyBatchItem::STATUS_BUNDLE);

        $skippedStatuses = [
            WeeklyBatchItem::STATUS_OUT_OF_RANGE,
            WeeklyBatchItem::STATUS_ALREADY_IN_DB,
            WeeklyBatchItem::STATUS_BUNDLE,
            WeeklyBatchItem::STATUS_LOW_QUALITY,
            WeeklyBatchItem::STATUS_EXCLUDED,
        ];
        $fetchItemsByPage = $allItems
            ->filter(fn($i) => !in_array($i->item_status, $skippedStatuses))
            ->groupBy('page_number');

        $bindings['Batch']            = $batch;
        $bindings['Console']          = $console;
        $bindings['ListType']         = $listType;
        $bindings['Label']            = $label;
        $bindings['RawPages']         = $rawPages;
        $bindings['Counts']           = $counts;
        $bindings['FetchItemsByPage'] = $fetchItemsByPage;
        $bindings['LqItems']          = $lqItems;
        $bindings['LqAutoItems']      = $lqAutoItems;
        $bindings['BundleItems']      = $bundleItems;
        $bindings['Stages']         = $this->getStages($batchId, $console, $listType, count($rawPages), $counts);
        $bindings['CurrentStage']   = 'fetch';

        return view('staff.games.weekly-updates.list.fetch', $bindings);
    }

    public function fetchItem($batchId, $console, $listType, $itemId)
    {
        $item = $this->repoItem->find((int) $itemId);

        if (!$item || $item->batch_id != $batchId) {
            return response()->json(['status' => 'error', 'message' => 'Item not found'], 404);
        }

        if ($item->item_status !== WeeklyBatchItem::STATUS_FETCH_PENDING) {
            return response()->json(['status' => 'skipped', 'item_status' => $item->item_status]);
        }

        if (!$item->nintendo_url) {
            $this->repoItem->markFetchFailed($item, 'No URL set');
            return response()->json(['status' => 'failed', 'message' => 'No URL set']);
        }

        try {
            $result = $this->fetcher->fetch($item->nintendo_url);

            $fetchReason    = $result['lq_flag_reason'];
            $existingReason = ($item->lq_flag && $item->lq_flag_reason) ? $item->lq_flag_reason : null;

            $parts = [];
            if ($existingReason) {
                $parts = array_map('trim', explode(';', $existingReason));
            }
            if ($fetchReason) {
                $parts = array_merge($parts, array_map('trim', explode(';', $fetchReason)));
            }
            $flagReason = implode('; ', array_unique(array_filter($parts))) ?: null;

            $data = [
                'publisher_raw'        => $result['publisher_raw'],
                'publisher_normalised' => $result['publisher_normalised'],
                'players'              => $result['players'],
                'description'          => $result['description'],
                'lq_flag'              => ($result['lq_confirmed'] || $result['lq_uncertain'] || $item->lq_flag) ? 1 : 0,
                'lq_flag_reason'       => $flagReason ?: null,
                'lq_publisher_name'    => $result['lq_publisher_name'],
            ];

            $this->repoItem->markFetchComplete($item, $data, $result['lq_confirmed']);

            return response()->json([
                'status'       => 'done',
                'item_status'  => $item->fresh()->item_status,
                'publisher'    => $result['publisher_normalised'],
                'players'      => $result['players'],
                'lq_confirmed' => $result['lq_confirmed'],
                'lq_uncertain' => $result['lq_uncertain'],
                'lq_reason'    => $flagReason,
            ]);

        } catch (\LogicException $e) {
            if (str_contains($e->getMessage(), 'redirections was reached')) {
                $this->repoItem->markFetchFailed($item, 'Too many redirects');
                return response()->json(['status' => 'failed', 'message' => 'Too many redirects']);
            }
            throw $e;
        } catch (\Exception $e) {
            $this->repoItem->markFetchFailed($item, $e->getMessage());
            return response()->json(['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    public function saveLqDecisions($batchId, $console, $listType)
    {
        $this->getBatch($batchId);

        // decisions[itemId] = 'low_quality' | 'bundle' | 'keep'
        $decisions = request('decisions', []);

        foreach ($decisions as $itemId => $decision) {
            $item = $this->repoItem->find((int) $itemId);
            if (!$item || $item->batch_id != $batchId) continue;
            if ($item->item_status !== WeeklyBatchItem::STATUS_LQ_REVIEW) continue;

            match ($decision) {
                'low_quality' => $this->repoItem->markLowQuality($item),
                'bundle'      => $this->repoItem->markBundle($item),
                'keep'        => $this->repoItem->keepDespiteLqFlag($item),
                default       => null,
            };
        }

        return redirect()->route('staff.games.weekly-updates.list.fetch', compact('batchId', 'console', 'listType'))
            ->with('success', 'LQ decisions saved.');
    }

    public function itemAction($batchId, $console, $listType, $itemId, \Illuminate\Http\Request $request)
    {
        $item = $this->repoItem->find((int) $itemId);

        if (!$item || $item->batch_id != $batchId) abort(404);
        if (in_array($item->item_status, [\App\Models\WeeklyBatchItem::STATUS_ALREADY_IN_DB, \App\Models\WeeklyBatchItem::STATUS_OUT_OF_RANGE, \App\Models\WeeklyBatchItem::STATUS_IMPORTED])) {
            abort(422);
        }

        $action = $request->input('action');

        $reparse = $action === 'reparse';

        match($action) {
            'low_quality' => $this->repoItem->markLowQuality($item),
            'bundle'      => $this->repoItem->markBundle($item),
            'excluded'    => $this->repoItem->excludeItem($item),
            'reset'       => $this->repoItem->resetItem($item),
            'reparse'     => $this->parseService->reparseItem($item),
            default       => null,
        };

        if (!$reparse && $request->ajax()) {
            $item->refresh();
            return response()->json(['status' => 'ok', 'new_status' => $item->item_status]);
        }

        return redirect()->back();
    }

    // ---- Packshot collection ----

    public function packshots($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Packshots';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $rawPages = $this->repoRawPage->getForList($batchId, $console, $listType);
        $allItems = $this->repoItem->getForList($batchId, $console, $listType);
        $counts   = $this->repoItem->getStatusCounts($batchId, $console, $listType);

        $itemsByPage = $allItems->groupBy('page_number');

        $bindings['Batch']        = $batch;
        $bindings['Console']      = $console;
        $bindings['ListType']     = $listType;
        $bindings['Label']        = $label;
        $bindings['RawPages']     = $rawPages;
        $bindings['ItemsByPage']  = $itemsByPage;
        $bindings['Counts']       = $counts;
        $bindings['Stages']       = $this->getStages($batchId, $console, $listType, count($rawPages), $counts);
        $bindings['CurrentStage'] = 'packshots';

        return view('staff.games.weekly-updates.list.packshots', $bindings);
    }

    public function savePackshots($batchId, $console, $listType, \Illuminate\Http\Request $request)
    {
        $this->getBatch($batchId);

        $packshots = $request->input('packshots', []);
        $saved = 0;

        foreach ($packshots as $itemId => $url) {
            $url = trim($url);
            if ($url === '') continue;

            $item = $this->repoItem->find((int) $itemId);
            if (!$item || $item->batch_id != $batchId) continue;

            $this->repoItem->updatePackshot($item, $url);
            $saved++;
        }

        return redirect()->route('staff.games.weekly-updates.list.packshots', compact('batchId', 'console', 'listType'))
            ->with('success', "{$saved} packshot".($saved !== 1 ? 's' : '')." saved.");
    }

    // ---- Category review ----

    public function categories($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Categories';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $counts       = $this->repoItem->getStatusCounts($batchId, $console, $listType);
        $rawPageCount = $this->repoRawPage->countForList($batchId, $console, $listType);
        $rawPages     = $this->repoRawPage->getForList($batchId, $console, $listType);

        $activeItems = $this->repoItem->getActiveForList($batchId, $console, $listType)
            ->whereIn('item_status', [
                WeeklyBatchItem::STATUS_CATEGORY_PENDING,
                WeeklyBatchItem::STATUS_READY,
            ]);

        // Run suggestions; update DB whenever the result changes. Build metadata map keyed by item ID.
        $suggestionMeta = [];
        foreach ($activeItems as $item) {
            $suggestion = $this->categorySuggester->suggest($item);
            if ($item->suggested_category !== $suggestion['category']) {
                $this->repoItem->updateSuggestedCategory($item, $suggestion['category']);
                $item->suggested_category = $suggestion['category'];
            }
            $suggestionMeta[$item->id] = [
                'category'   => $suggestion['category'],
                'confidence' => $suggestion['confidence'],
                'score'      => $suggestion['score'],
                'reason'     => $suggestion['reason'],
            ];
        }

        // Build grouped category list for the dropdown
        $allCategories  = $this->repoCategory->getAll();
        $topLevel       = $allCategories->whereNull('parent_id')->sortBy('name');
        $categoryGroups = [];
        foreach ($topLevel as $parent) {
            $children = $allCategories->where('parent_id', $parent->id)->sortBy('name');
            $categoryGroups[] = [
                'parent'   => $parent->name,
                'children' => $children->values(),
            ];
        }

        $bindings['Batch']           = $batch;
        $bindings['Console']         = $console;
        $bindings['ListType']        = $listType;
        $bindings['Label']           = $label;
        $bindings['Items']           = $activeItems;
        $bindings['SuggestionMeta']  = $suggestionMeta;
        $bindings['CategoryGroups']  = $categoryGroups;
        $bindings['Counts']          = $counts;
        $bindings['RawPages']        = $rawPages;
        $bindings['Stages']          = $this->getStages($batchId, $console, $listType, $rawPageCount, $counts);
        $bindings['CurrentStage']    = 'categories';

        return view('staff.games.weekly-updates.list.categories', $bindings);
    }

    public function saveItemCategory($batchId, $console, $listType, $itemId, \Illuminate\Http\Request $request)
    {
        $this->getBatch($batchId);

        $item = $this->repoItem->find((int) $itemId);
        if (!$item || $item->batch_id != $batchId) abort(404);
        if (!in_array($item->item_status, [WeeklyBatchItem::STATUS_CATEGORY_PENDING, WeeklyBatchItem::STATUS_READY])) abort(422);

        $categoryName = trim($request->input('category', ''));
        if ($categoryName === '') abort(422);

        $accepted = $item->suggested_category !== null && $categoryName === $item->suggested_category;
        $this->repoItem->updateCategory($item, $categoryName, $accepted);

        return response()->json(['status' => 'ok', 'accepted' => $accepted]);
    }

    public function resetCategories($batchId, $console, $listType)
    {
        $this->getBatch($batchId);
        $this->repoItem->clearAllCategories($batchId, $console, $listType);

        return redirect()->route('staff.games.weekly-updates.list.categories', compact('batchId', 'console', 'listType'))
            ->with('success', 'All categories reset.');
    }

    public function acceptCategory($batchId, $console, $listType, $itemId)
    {
        $this->getBatch($batchId);

        $item = $this->repoItem->find((int) $itemId);
        if (!$item || $item->batch_id != $batchId) abort(404);
        if (!in_array($item->item_status, [WeeklyBatchItem::STATUS_CATEGORY_PENDING, WeeklyBatchItem::STATUS_READY])) abort(422);
        if (!$item->suggested_category) abort(422);

        $this->repoItem->updateCategory($item, $item->suggested_category, true);

        return response()->json(['status' => 'ok']);
    }

    public function saveItemPublisher($batchId, $console, $listType, $itemId, \Illuminate\Http\Request $request)
    {
        $this->getBatch($batchId);

        $item = $this->repoItem->find((int) $itemId);
        if (!$item || $item->batch_id != $batchId) abort(404);

        $name = trim($request->input('publisher', ''));
        if ($name === '') abort(422);

        $item->publisher_normalised = $name;
        $item->save();

        $exists = $this->repoGamesCompany->findByNameCaseInsensitive($name) !== null;

        return response()->json(['status' => 'ok', 'exists' => $exists, 'name' => $name]);
    }

    // ---- Confirm & import ----

    public function confirm($batchId, $console, $listType)
    {
        $batch = $this->getBatch($batchId);
        $label = $this->listLabel($console, $listType);

        $pageTitle = $label.' - Confirm and import';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::weeklyUpdatesListSubpage($pageTitle, $batch))->bindings;

        $readyItems    = $this->repoItem->getReadyForImport($batchId, $console, $listType);
        $importedItems = $this->repoItem->getByStatus($batchId, $console, $listType, WeeklyBatchItem::STATUS_IMPORTED);
        $counts        = $this->repoItem->getStatusCounts($batchId, $console, $listType);
        $rawPageCount  = $this->repoRawPage->countForList($batchId, $console, $listType);

        // Build Release Hub link for this batch
        $batchDate   = $batch->batch_date;
        $consoleId   = $console === 'switch-2' ? 2 : 1;
        $customStart = $batchDate->copy()->subDays(6)->toDateString();
        $customEnd   = $batchDate->toDateString();
        if ($listType === 'new') {
            $hubParams = http_build_query([
                'consoleId'   => $consoleId,
                'startDate'   => $customStart,
                'endDate'     => $customEnd,
                'customStart' => $customStart,
                'customEnd'   => $customEnd,
                'sort'        => 'desc',
            ]);
        } else {
            $hubParams = http_build_query([
                'consoleId'   => $consoleId,
                'startDate'   => $batchDate->copy()->addDay()->toDateString(),
                'endDate'     => $batchDate->copy()->addDays(7)->toDateString(),
                'customStart' => $customStart,
                'customEnd'   => $customEnd,
            ]);
        }
        $releaseHubUrl = route('staff.games.release-hub.show') . '?' . $hubParams;

        // Publishers missing from DB — need to visit the Publishers step first
        $missingPublishers = [];
        foreach ($readyItems as $item) {
            $name = $item->publisher_normalised;
            if ($name && !isset($missingPublishers[$name])) {
                $exists = $this->repoGamesCompany->findByNameCaseInsensitive($name) !== null;
                if (!$exists) {
                    $missingPublishers[] = $name;
                }
            }
        }

        // LQ reminder: low_quality items where the publisher is in the DB but not flagged as LQ
        $lqItems = $this->repoItem->getByStatus($batchId, $console, $listType, WeeklyBatchItem::STATUS_LOW_QUALITY);
        $lqReminderPublishers = [];
        foreach ($lqItems as $item) {
            $name = $item->publisher_normalised;
            if ($name && !isset($lqReminderPublishers[$name])) {
                $company = $this->repoGamesCompany->findByNameCaseInsensitive($name);
                if ($company && !$company->is_low_quality) {
                    $lqReminderPublishers[$name] = $company->id;
                }
            }
        }

        $bindings['Batch']                  = $batch;
        $bindings['Console']                = $console;
        $bindings['ListType']               = $listType;
        $bindings['Label']                  = $label;
        $bindings['ReadyItems']             = $readyItems;
        $bindings['ImportedItems']          = $importedItems;
        $bindings['Counts']                 = $counts;
        $bindings['MissingPublishers']      = $missingPublishers;
        $bindings['LqReminderPublishers']   = $lqReminderPublishers;
        $bindings['ReleaseHubUrl']          = $releaseHubUrl;
        $bindings['Stages']                 = $this->getStages($batchId, $console, $listType, $rawPageCount, $counts);
        $bindings['CurrentStage']           = 'confirm';

        return view('staff.games.weekly-updates.list.confirm', $bindings);
    }

    public function import($batchId, $console, $listType)
    {
        $this->getBatch($batchId);

        $readyItems = $this->repoItem->getReadyForImport($batchId, $console, $listType);

        // Build a full ordering sequence using all items (including already_in_db) so that
        // existing games and new games share a consistent eshop_europe_order per release date.
        $allOrderedItems = $this->repoItem->getForList($batchId, $console, $listType)
            ->whereIn('item_status', [
                WeeklyBatchItem::STATUS_ALREADY_IN_DB,
                WeeklyBatchItem::STATUS_READY,
            ])
            ->sortBy('sort_order')
            ->sortBy('page_number');

        $orderByDate = [];
        $eshopOrderMap = []; // item_id → eshop_europe_order

        foreach ($allOrderedItems as $item) {
            $date = $item->release_date?->toDateString();
            if ($date) {
                $orderByDate[$date] = ($orderByDate[$date] ?? 0) + 1;
                $eshopOrderMap[$item->id] = $orderByDate[$date];
            }
        }

        // Update eshop_europe_order on already_in_db games now that we know their position
        foreach ($allOrderedItems->where('item_status', WeeklyBatchItem::STATUS_ALREADY_IN_DB) as $item) {
            if ($item->game_id && isset($eshopOrderMap[$item->id])) {
                $this->repoGame->updateEshopOrder($item->game_id, $eshopOrderMap[$item->id]);
            }
        }

        $imported       = 0;
        $errors         = [];
        $packshotFailed = [];
        $headerFailed   = [];

        foreach ($readyItems as $item) {
            $eshopOrder = $eshopOrderMap[$item->id] ?? null;

            try {
                $result = $this->gameImporter->importItem($item, $eshopOrder);
                $this->repoItem->markImported($item, $result['game']->id);
                $imported++;

                if ($item->packshot_url && !$result['packshot_ok']) {
                    $packshotFailed[] = $item->title;
                }
                if ($item->nintendo_url && !$result['header_ok']) {
                    $headerFailed[] = $item->title;
                }
            } catch (\Exception $e) {
                $errors[] = "{$item->title}: {$e->getMessage()}";
            }
        }

        $parts = ["{$imported} game".($imported !== 1 ? 's' : '')." imported."];

        if ($packshotFailed) {
            $parts[] = 'Packshot failed: '.implode(', ', $packshotFailed).'.';
        }
        if ($headerFailed) {
            $parts[] = 'Header image failed: '.implode(', ', $headerFailed).'.';
        }

        $message = implode(' ', $parts);

        if ($errors) {
            $message .= ' Errors: '.implode('; ', $errors);
            return redirect()->route('staff.games.weekly-updates.list.confirm', compact('batchId', 'console', 'listType'))
                ->with('error', $message);
        }

        return redirect()->route('staff.games.weekly-updates.list.confirm', compact('batchId', 'console', 'listType'))
            ->with('success', $message);
    }
}
