<?php

namespace App\Domain\Category\Repository;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Categories for the public browse pages.
 */
class CategoryBrowseRepository
{
    /**
     * Top-level categories with their children, each with a games_count. The counts come from
     * the database rather than loading each category's games: nearly every game has a category,
     * so loading them to count them ran the landing page out of memory.
     */
    public function topLevelWithGameCounts(): Collection
    {
        return Category::whereDoesntHave('parent')
            ->withCount('games')
            ->with(['children' => fn ($query) => $query->withCount('games')])
            ->orderBy('name', 'asc')
            ->get();
    }
}
