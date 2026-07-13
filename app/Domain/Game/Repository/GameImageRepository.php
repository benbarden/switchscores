<?php

namespace App\Domain\Game\Repository;

use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read queries for the Game images dashboard (packshot storage migration stats)
 * and the migration tool (unmigrated / recently-migrated lists).
 */
class GameImageRepository
{
    /** Games that have at least one packshot (square or header). */
    public function countGamesWithImages(): int
    {
        return Game::where($this->hasImage())->count();
    }

    /** Games with no packshot at all. */
    public function countGamesWithoutImages(): int
    {
        return Game::whereNull('image_square')->whereNull('image_header')->count();
    }

    /**
     * Games with images that are NOT yet in spaces (no game_images spaces row),
     * oldest first, optionally filtered by console. Paginated for the migration tool.
     */
    public function paginateUnmigrated(?int $consoleId, int $perPage): LengthAwarePaginator
    {
        return $this->unmigratedQuery($consoleId)
            ->with('images')
            ->paginate($perPage)
            ->withQueryString();
    }

    /** The next batch of oldest unmigrated games (for the "migrate next N" action). */
    public function nextUnmigratedBatch(?int $consoleId, int $limit): Collection
    {
        return $this->unmigratedQuery($consoleId)->limit($limit)->get();
    }

    /** Migrated games (spaces), newest first, paginated — for the undo list. */
    public function paginateRecentlyMigrated(int $perPage): LengthAwarePaginator
    {
        return GameImage::where('location', GameImage::LOCATION_SPACES)
            ->with('game')
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    private function unmigratedQuery(?int $consoleId)
    {
        return Game::where($this->hasImage())
            ->whereDoesntHave('images', function ($query) {
                $query->where('location', GameImage::LOCATION_SPACES);
            })
            ->when($consoleId, fn ($query) => $query->where('console_id', $consoleId))
            ->orderBy('id');
    }

    /** Games whose packshots have been migrated to object storage. */
    public function countInSpaces(): int
    {
        return GameImage::where('location', GameImage::LOCATION_SPACES)->count();
    }

    /** [console_id => count of games with images]. */
    public function withImagesByConsole(): array
    {
        return Game::selectRaw('console_id, count(*) as c')
            ->where($this->hasImage())
            ->groupBy('console_id')
            ->pluck('c', 'console_id')
            ->toArray();
    }

    /** [console_id => count of games in spaces]. */
    public function inSpacesByConsole(): array
    {
        return DB::table('game_images')
            ->join('games', 'games.id', '=', 'game_images.game_id')
            ->where('game_images.location', GameImage::LOCATION_SPACES)
            ->selectRaw('games.console_id, count(*) as c')
            ->groupBy('games.console_id')
            ->pluck('c', 'console_id')
            ->toArray();
    }

    private function hasImage(): \Closure
    {
        return function ($query) {
            $query->whereNotNull('image_square')->orWhereNotNull('image_header');
        };
    }
}
