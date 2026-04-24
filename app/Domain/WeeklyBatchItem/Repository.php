<?php

namespace App\Domain\WeeklyBatchItem;

use App\Models\WeeklyBatchItem;
use Illuminate\Support\Collection;

class Repository
{
    public function create(array $data): WeeklyBatchItem
    {
        $item = new WeeklyBatchItem();
        $item->fill($data);
        $item->save();
        return $item;
    }

    public function find(int $id): ?WeeklyBatchItem
    {
        return WeeklyBatchItem::find($id);
    }

    public function getForList(int $batchId, string $console, string $listType): Collection
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->orderBy('page_number')
            ->orderBy('sort_order')
            ->get();
    }

    public function getForListPage(int $batchId, string $console, string $listType, int $pageNumber): Collection
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('page_number', $pageNumber)
            ->orderBy('sort_order')
            ->get();
    }

    public function getActiveForList(int $batchId, string $console, string $listType): Collection
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->whereIn('item_status', WeeklyBatchItem::ACTIVE_STATUSES)
            ->orderBy('page_number')
            ->orderBy('sort_order')
            ->get();
    }

    public function getByStatus(int $batchId, string $console, string $listType, string $status): Collection
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('item_status', $status)
            ->orderBy('page_number')
            ->orderBy('sort_order')
            ->get();
    }

    public function getNeedingLqReview(int $batchId, string $console, string $listType): Collection
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('item_status', WeeklyBatchItem::STATUS_LQ_REVIEW)
            ->orderBy('page_number')
            ->orderBy('sort_order')
            ->get();
    }

    public function getReadyForImport(int $batchId, string $console, string $listType): Collection
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('item_status', WeeklyBatchItem::STATUS_READY)
            ->orderBy('page_number')
            ->orderBy('sort_order')
            ->get();
    }

    public function getStatusCounts(int $batchId, string $console, string $listType): array
    {
        $rows = WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->selectRaw('item_status, count(*) as total')
            ->groupBy('item_status')
            ->pluck('total', 'item_status')
            ->toArray();

        return [
            'already_in_db'    => $rows[WeeklyBatchItem::STATUS_ALREADY_IN_DB]  ?? 0,
            'out_of_range'     => $rows[WeeklyBatchItem::STATUS_OUT_OF_RANGE]   ?? 0,
            'low_quality'      => $rows[WeeklyBatchItem::STATUS_LOW_QUALITY]    ?? 0,
            'bundle'           => $rows[WeeklyBatchItem::STATUS_BUNDLE]         ?? 0,
            'excluded'         => $rows[WeeklyBatchItem::STATUS_EXCLUDED]       ?? 0,
            'pending'          => $rows[WeeklyBatchItem::STATUS_PENDING]        ?? 0,
            'fetch_pending'    => $rows[WeeklyBatchItem::STATUS_FETCH_PENDING]  ?? 0,
            'lq_review'        => $rows[WeeklyBatchItem::STATUS_LQ_REVIEW]      ?? 0,
            'packshot_pending' => $rows[WeeklyBatchItem::STATUS_PACKSHOT_PENDING] ?? 0,
            'category_pending' => $rows[WeeklyBatchItem::STATUS_CATEGORY_PENDING] ?? 0,
            'ready'            => $rows[WeeklyBatchItem::STATUS_READY]          ?? 0,
            'imported'         => $rows[WeeklyBatchItem::STATUS_IMPORTED]       ?? 0,
        ];
    }

    public function getAllStatusCountsForBatches(array $batchIds): array
    {
        $rows = WeeklyBatchItem::whereIn('batch_id', $batchIds)
            ->selectRaw('batch_id, console, list_type, item_status, count(*) as total')
            ->groupBy('batch_id', 'console', 'list_type', 'item_status')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row->batch_id][$row->console][$row->list_type][$row->item_status] = $row->total;
        }
        return $counts;
    }

    public function getAllStatusCounts(int $batchId): array
    {
        $rows = WeeklyBatchItem::where('batch_id', $batchId)
            ->selectRaw('console, list_type, item_status, count(*) as total')
            ->groupBy('console', 'list_type', 'item_status')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row->console][$row->list_type][$row->item_status] = $row->total;
        }
        return $counts;
    }

    public function updateStatus(WeeklyBatchItem $item, string $status): void
    {
        $item->item_status = $status;
        $item->save();
    }

    public function clearSalePriceFlags(int $batchId, string $console, string $listType): void
    {
        WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('price_flag', true)
            ->where('price_flag_reason', 'like', 'Sale price detected%')
            ->update(['price_flag' => false, 'price_flag_reason' => null]);
    }

    public function updatePrice(WeeklyBatchItem $item, float $price): void
    {
        $item->price_gbp        = $price;
        $item->price_flag       = false;
        $item->price_flag_reason = null;
        $item->save();
    }

    public function updateUrl(WeeklyBatchItem $item, string $url): void
    {
        $item->nintendo_url = $url;
        $item->item_status  = WeeklyBatchItem::STATUS_FETCH_PENDING;
        $item->fetch_status = WeeklyBatchItem::FETCH_STATUS_PENDING;
        $item->save();
    }

    public function clearUrl(WeeklyBatchItem $item): void
    {
        $item->nintendo_url = null;
        $item->item_status  = WeeklyBatchItem::STATUS_PENDING;
        $item->fetch_status = null;
        $item->save();
    }

    public function updatePackshot(WeeklyBatchItem $item, string $url): void
    {
        $item->packshot_url = $url;
        $item->item_status  = WeeklyBatchItem::STATUS_CATEGORY_PENDING;
        $item->save();
    }

    public function updateCategory(WeeklyBatchItem $item, string $category, ?bool $accepted = null): void
    {
        $item->category    = $category;
        $item->item_status = WeeklyBatchItem::STATUS_READY;
        if ($accepted !== null) {
            $item->suggestion_accepted = $accepted ? 1 : 0;
        }
        $item->save();
    }

    public function markFetchQueued(WeeklyBatchItem $item): void
    {
        $item->fetch_status = WeeklyBatchItem::FETCH_STATUS_QUEUED;
        $item->save();
    }

    public function markFetchComplete(WeeklyBatchItem $item, array $data, bool $confirmedLq = false): void
    {
        $item->fill($data);
        $item->fetch_status = WeeklyBatchItem::FETCH_STATUS_FETCHED;

        if ($confirmedLq) {
            $item->item_status = WeeklyBatchItem::STATUS_LOW_QUALITY;
        } elseif ($item->lq_flag && !$item->packshot_url) {
            // lq_flag set and no packshot yet — user hasn't reviewed this item
            $item->item_status = WeeklyBatchItem::STATUS_LQ_REVIEW;
        } elseif ($item->packshot_url && $item->category) {
            $item->item_status = WeeklyBatchItem::STATUS_READY;
        } elseif ($item->packshot_url) {
            $item->item_status = WeeklyBatchItem::STATUS_CATEGORY_PENDING;
        } else {
            $item->item_status = WeeklyBatchItem::STATUS_PACKSHOT_PENDING;
        }

        $item->save();
    }

    public function markFetchFailed(WeeklyBatchItem $item, string $error): void
    {
        $item->fetch_status = WeeklyBatchItem::FETCH_STATUS_FAILED;
        $item->fetch_error  = $error;
        $item->save();
    }

    public function excludeItem(WeeklyBatchItem $item): void
    {
        $item->item_status = WeeklyBatchItem::STATUS_EXCLUDED;
        $item->save();
    }

    public function resetItem(WeeklyBatchItem $item): void
    {
        if ($item->nintendo_url) {
            $item->item_status = WeeklyBatchItem::STATUS_FETCH_PENDING;
            $item->fetch_status = WeeklyBatchItem::FETCH_STATUS_PENDING;
        } else {
            $item->item_status = WeeklyBatchItem::STATUS_PENDING;
            $item->fetch_status = null;
        }
        $item->save();
    }

    public function markLowQuality(WeeklyBatchItem $item): void
    {
        $item->item_status = WeeklyBatchItem::STATUS_LOW_QUALITY;
        $item->save();
    }

    public function markBundle(WeeklyBatchItem $item): void
    {
        $item->item_status = WeeklyBatchItem::STATUS_BUNDLE;
        $item->save();
    }

    public function keepDespiteLqFlag(WeeklyBatchItem $item): void
    {
        $item->item_status = WeeklyBatchItem::STATUS_PACKSHOT_PENDING;
        $item->save();
    }

    public function markImported(WeeklyBatchItem $item, int $gameId): void
    {
        $item->item_status = WeeklyBatchItem::STATUS_IMPORTED;
        $item->game_id     = $gameId;
        $item->save();
    }

    public function hasAdvancedItems(int $batchId, string $console, string $listType, int $pageNumber): bool
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('page_number', $pageNumber)
            ->whereIn('item_status', WeeklyBatchItem::ADVANCED_STATUSES)
            ->exists();
    }

    public function restoreSnapshot(WeeklyBatchItem $item, WeeklyBatchItem $snapshot): void
    {
        $noActionTaken = in_array($snapshot->item_status, [
            WeeklyBatchItem::STATUS_PENDING,
            WeeklyBatchItem::STATUS_ALREADY_IN_DB,
            WeeklyBatchItem::STATUS_OUT_OF_RANGE,
        ]) && !$snapshot->nintendo_url;

        if ($noActionTaken) {
            return;
        }

        if ($snapshot->nintendo_url) {
            $item->nintendo_url         = $snapshot->nintendo_url;
            $item->fetch_status         = $snapshot->fetch_status;
            $item->fetch_error          = $snapshot->fetch_error;
            $item->publisher_raw        = $snapshot->publisher_raw;
            $item->publisher_normalised = $snapshot->publisher_normalised;
            $item->players              = $snapshot->players;
            $item->lq_publisher_name    = $snapshot->lq_publisher_name;
            $item->packshot_url         = $snapshot->packshot_url;
        }

        $restorableStatuses = [
            WeeklyBatchItem::STATUS_FETCH_PENDING,
            WeeklyBatchItem::STATUS_LQ_REVIEW,
            WeeklyBatchItem::STATUS_LOW_QUALITY,
            WeeklyBatchItem::STATUS_BUNDLE,
            WeeklyBatchItem::STATUS_EXCLUDED,
            WeeklyBatchItem::STATUS_PACKSHOT_PENDING,
            WeeklyBatchItem::STATUS_CATEGORY_PENDING,
            WeeklyBatchItem::STATUS_READY,
        ];

        if (in_array($snapshot->item_status, $restorableStatuses)) {
            $item->item_status = $snapshot->item_status;
        }

        $item->save();
    }

    public function deleteForListPage(int $batchId, string $console, string $listType, int $pageNumber): void
    {
        WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('page_number', $pageNumber)
            ->delete();
    }

    /**
     * Look up the most commonly assigned category for a publisher across previous batches.
     * Returns the category name or null if no history found.
     */
    public function getCategoryHistory(string $publisherNormalised, int $excludeBatchId): ?string
    {
        $row = WeeklyBatchItem::where('publisher_normalised', $publisherNormalised)
            ->where('batch_id', '!=', $excludeBatchId)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        return $row?->category;
    }

    public function clearAllCategories(int $batchId, string $console, string $listType): void
    {
        WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->whereIn('item_status', [WeeklyBatchItem::STATUS_READY, WeeklyBatchItem::STATUS_CATEGORY_PENDING])
            ->update([
                'category'           => null,
                'suggestion_accepted' => null,
                'item_status'        => WeeklyBatchItem::STATUS_CATEGORY_PENDING,
            ]);
    }

    public function updateSuggestedCategory(WeeklyBatchItem $item, ?string $category): void
    {
        $item->suggested_category = $category;
        $item->save();
    }

    public function renamePublisher(int $batchId, string $console, string $listType, string $oldName, string $newName): void
    {
        WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('publisher_normalised', $oldName)
            ->update(['publisher_normalised' => $newName]);
    }

    public function markPublisherItemsLq(int $batchId, string $console, string $listType, string $publisherName): int
    {
        return WeeklyBatchItem::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('publisher_normalised', $publisherName)
            ->whereIn('item_status', [
                WeeklyBatchItem::STATUS_LQ_REVIEW,
                WeeklyBatchItem::STATUS_PACKSHOT_PENDING,
                WeeklyBatchItem::STATUS_CATEGORY_PENDING,
                WeeklyBatchItem::STATUS_READY,
            ])
            ->update(['item_status' => WeeklyBatchItem::STATUS_LOW_QUALITY]);
    }

    public function getCategoryHistoryByCollection(string $collection, int $excludeBatchId): ?string
    {
        $row = WeeklyBatchItem::where('collection', $collection)
            ->where('batch_id', '!=', $excludeBatchId)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->first();

        return $row?->category;
    }
}
