<?php

namespace App\Domain\WeeklyBatchRawPage;

use App\Models\WeeklyBatchRawPage;
use Illuminate\Support\Collection;

class Repository
{
    public function saveOrReplace(int $batchId, string $console, string $listType, int $pageNumber, string $rawContent): WeeklyBatchRawPage
    {
        $page = WeeklyBatchRawPage::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('page_number', $pageNumber)
            ->first();

        if (!$page) {
            $page = new WeeklyBatchRawPage();
            $page->batch_id    = $batchId;
            $page->console     = $console;
            $page->list_type   = $listType;
            $page->page_number = $pageNumber;
        }

        $page->raw_content = $rawContent;
        $page->parsed_at   = null; // reset on replace
        $page->save();
        return $page;
    }

    public function markParsed(WeeklyBatchRawPage $page): void
    {
        $page->parsed_at = now();
        $page->save();
    }

    public function remove(int $batchId, string $console, string $listType, int $pageNumber): void
    {
        WeeklyBatchRawPage::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('page_number', $pageNumber)
            ->delete();
    }

    public function getForList(int $batchId, string $console, string $listType): Collection
    {
        return WeeklyBatchRawPage::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->orderBy('page_number')
            ->get();
    }

    public function find(int $batchId, string $console, string $listType, int $pageNumber): ?WeeklyBatchRawPage
    {
        return WeeklyBatchRawPage::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->where('page_number', $pageNumber)
            ->first();
    }

    public function getCountsForBatch(int $batchId): array
    {
        $rows = WeeklyBatchRawPage::where('batch_id', $batchId)
            ->selectRaw('console, list_type, count(*) as total')
            ->groupBy('console', 'list_type')
            ->get();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row->console][$row->list_type] = $row->total;
        }
        return $counts;
    }

    public function countForList(int $batchId, string $console, string $listType): int
    {
        return WeeklyBatchRawPage::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->count();
    }

    public function getNextPageNumber(int $batchId, string $console, string $listType): int
    {
        $max = WeeklyBatchRawPage::where('batch_id', $batchId)
            ->where('console', $console)
            ->where('list_type', $listType)
            ->max('page_number');
        return ($max ?? 0) + 1;
    }
}
