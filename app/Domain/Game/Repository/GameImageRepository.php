<?php

namespace App\Domain\Game\Repository;

use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Read queries for the Game images dashboard (packshot storage migration stats)
 * and the migration tool (unmigrated / recently-migrated lists).
 *
 * Every count here answers a question about GAMES, and about the same population of
 * games. That is deliberate, and it is the fix for a dashboard that read 100.0%
 * migrated while packshots were being written with no file extension, then drifted
 * past 100% entirely (100.4% on prod, 2026-08-07).
 *
 * The original bug: the denominator counted games with non-null LEGACY columns while
 * the numerator counted `game_images` rows. A correctly-migrated game has null legacy
 * columns, so it joined the numerator and left the denominator. Early on that rounded
 * away against ~16.9k games; as migration progressed the numerator overtook the
 * denominator and the percentage went above 100. Neither reading could ever have shown
 * a handful of bad writes, which is what the dashboard existed to catch.
 *
 * So: "has a packshot" now means legacy columns OR a stored `game_images` filename,
 * and "migrated" means a game with a spaces row - not a row count.
 */
class GameImageRepository
{
    /** Games that have at least one packshot (square or header), in EITHER storage. */
    public function countGamesWithImages(): int
    {
        return Game::where($this->hasAnyImage())->count();
    }

    /** Games with no packshot at all, in either storage. */
    public function countGamesWithoutImages(): int
    {
        return Game::whereNull('image_square')
            ->whereNull('image_header')
            ->whereDoesntHave('images', $this->hasStoredFilename())
            ->count();
    }

    /**
     * Games whose packshots are still legacy-only - counted directly rather than
     * derived as (withImages - inSpaces).
     *
     * The old dashboard did that subtraction and clamped it with max(0, ...), which
     * meant the two ways it could go wrong - numerator and denominator counting
     * different things, and bad writes - both rendered as a confident 0.
     */
    public function countLegacyOnly(): int
    {
        return Game::where($this->hasImage())
            ->whereDoesntHave('images', $this->inSpaces())
            ->count();
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
            ->whereDoesntHave('images', $this->inSpaces())
            ->when($consoleId, fn ($query) => $query->where('console_id', $consoleId))
            ->orderBy('id');
    }

    /**
     * Games whose packshots have been migrated to object storage.
     *
     * Counts GAMES, not `game_images` rows, so it shares an entity with the
     * denominator. `images()` is a hasOne, so the two agree today - counting games
     * keeps them agreeing if a duplicate row is ever written.
     */
    public function countInSpaces(): int
    {
        return Game::whereHas('images', $this->inSpaces())->count();
    }

    /** [console_id => count of games with images, in either storage]. */
    public function withImagesByConsole(): array
    {
        return Game::selectRaw('console_id, count(*) as c')
            ->where($this->hasAnyImage())
            ->groupBy('console_id')
            ->pluck('c', 'console_id')
            ->toArray();
    }

    /** [console_id => count of games in spaces]. */
    public function inSpacesByConsole(): array
    {
        return Game::selectRaw('console_id, count(*) as c')
            ->whereHas('images', $this->inSpaces())
            ->groupBy('console_id')
            ->pluck('c', 'console_id')
            ->toArray();
    }

    /** [console_id => count of games still legacy-only]. */
    public function legacyOnlyByConsole(): array
    {
        return Game::selectRaw('console_id, count(*) as c')
            ->where($this->hasImage())
            ->whereDoesntHave('images', $this->inSpaces())
            ->groupBy('console_id')
            ->pluck('c', 'console_id')
            ->toArray();
    }

    /**
     * Legacy columns only - deliberately NOT hasAnyImage().
     *
     * "Still to migrate" means the image is on disk and not yet in object storage, so
     * the legacy columns are the right test. A spaces-only game is already migrated
     * and must not appear in the migration tool's work list.
     */
    private function hasImage(): \Closure
    {
        return function ($query) {
            $query->whereNotNull('image_square')->orWhereNotNull('image_header');
        };
    }

    /** Has a packshot in either storage - the honest denominator for "how many games have images". */
    private function hasAnyImage(): \Closure
    {
        return function ($query) {
            $query->whereNotNull('image_square')
                ->orWhereNotNull('image_header')
                ->orWhereHas('images', $this->hasStoredFilename());
        };
    }

    /**
     * A `game_images` row that actually names a file.
     *
     * The row existing is not the same as an image existing - a row with both
     * filenames null names nothing, and counting it would put a game in the
     * "has images" population on the strength of an empty record.
     */
    private function hasStoredFilename(): \Closure
    {
        return function ($query) {
            $query->whereNotNull('square_filename')->orWhereNotNull('header_filename');
        };
    }

    /**
     * Migrated to object storage: a spaces row that actually names a file.
     *
     * The filename check keeps the populations strictly nested - every game counted as
     * migrated is also counted by hasAnyImage() - so the percentage cannot exceed 100
     * again for the reason it just did. A spaces row with both filenames null names no
     * object, so such a game reads as legacy-only and stays in the migration work list,
     * which is the safe direction: it gets re-migrated rather than silently treated as
     * done.
     */
    private function inSpaces(): \Closure
    {
        return function ($query) {
            $query->where('location', GameImage::LOCATION_SPACES)
                ->where($this->hasStoredFilename());
        };
    }
}
