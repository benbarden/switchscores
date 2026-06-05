<?php

namespace App\Domain\WeeklyBatchExclusion;

use App\Models\WeeklyBatchExclusion;

class Repository
{
    public function isExcluded(string $title, string $console): bool
    {
        return WeeklyBatchExclusion::where('title', $title)
            ->where('console', $console)
            ->exists();
    }

    public function add(string $title, string $console, ?string $notes = null): void
    {
        WeeklyBatchExclusion::firstOrCreate(
            ['title' => $title, 'console' => $console],
            ['notes' => $notes]
        );
    }

    public function getPaginated(int $perPage = 25)
    {
        return WeeklyBatchExclusion::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?WeeklyBatchExclusion
    {
        return WeeklyBatchExclusion::find($id);
    }

    public function remove(int $id): void
    {
        WeeklyBatchExclusion::destroy($id);
    }
}
