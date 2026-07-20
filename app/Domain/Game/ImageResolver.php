<?php

namespace App\Domain\Game;

use App\Models\Console;
use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Support\Facades\Storage;

/**
 * Single source of truth for packshot (header/square) image URLs.
 *
 * Resolution order (Milestone 1):
 *   1. A `game_images` row with location = spaces + a filename -> serve from the `packshots` disk.
 *   2. Legacy `games.image_header` / `games.image_square` -> serve from /img/ps-{type}/.
 *   3. Empty string (no image) -> callers render a placeholder.
 *
 * With no `game_images` rows populated yet, every lookup falls through to legacy,
 * so the site renders identically. The §3 existence-based MinIO-override fallback
 * chain layers in later without changing this contract.
 *
 * Works for Eloquent `Game` models and for raw `DB::table('games')` rows whose query applied
 * `PackshotJoin` - the latter was added so public list pages (on-sale tabs, tag pages) can serve
 * object storage rather than being permanently pinned to the legacy path.
 */
class ImageResolver
{
    const TYPE_HEADER = 'header';
    const TYPE_SQUARE = 'square';

    const DISK = 'packshots';

    const LEGACY_PATHS = [
        self::TYPE_HEADER => '/img/ps-header/',
        self::TYPE_SQUARE => '/img/ps-square/',
    ];

    const CONSOLE_PREFIXES = [
        Console::ID_SWITCH_1 => 'switch-1',
        Console::ID_SWITCH_2 => 'switch-2',
    ];

    /**
     * Resolve the display URL for a game's header or square packshot.
     *
     * Accepts an Eloquent Game model or a raw DB row (stdClass); raw rows have no
     * `images` relation and therefore always resolve via the legacy path.
     *
     * @param object|null $game
     * @param string $type self::TYPE_HEADER | self::TYPE_SQUARE
     */
    public function url($game, string $type): string
    {
        if (!$game || !array_key_exists($type, self::LEGACY_PATHS)) {
            return '';
        }

        $image = $this->gameImage($game);

        if ($image && $image->location === GameImage::LOCATION_SPACES) {
            $spacesUrl = $this->spacesUrl($game, $image, $type);
            if ($spacesUrl !== '') {
                return $spacesUrl;
            }
        }

        return $this->legacyUrl($game, $type);
    }

    private function legacyUrl($game, string $type): string
    {
        $filename = $type === self::TYPE_HEADER
            ? ($game->image_header ?? null)
            : ($game->image_square ?? null);

        return $filename ? self::LEGACY_PATHS[$type] . $filename : '';
    }

    private function spacesUrl($game, GameImage $image, string $type): string
    {
        $filename = $type === self::TYPE_HEADER
            ? $image->header_filename
            : $image->square_filename;

        if (!$filename) {
            return '';
        }

        $url = Storage::disk(self::DISK)->url($this->storageKey($game, $type, $filename));

        $updatedAt = $type === self::TYPE_HEADER
            ? $image->header_updated_at
            : $image->square_updated_at;

        if ($updatedAt) {
            $url .= '?v=' . $updatedAt->timestamp;
        }

        return $url;
    }

    /**
     * Build the object key for a packshot on the `packshots` disk:
     * {console-prefix}/{type}/{filename}. Shared with ImageStorageMigrator so the
     * read (resolver) and write (migrator) sides can never disagree on layout.
     */
    public function storageKey($game, string $type, string $filename): string
    {
        $consoleId = $game->console_id ?? Console::ID_SWITCH_1;
        $prefix = self::CONSOLE_PREFIXES[$consoleId] ?? self::CONSOLE_PREFIXES[Console::ID_SWITCH_1];

        return "{$prefix}/{$type}/{$filename}";
    }

    /**
     * Return the GameImage row for a game without triggering an N+1 lazy load.
     *
     * Eloquent models carry the `images` relation. Raw rows (stdClass) have no relation, but
     * resolve correctly when the query applied PackshotJoin - see that class for why. A raw row
     * without the join still falls through to legacy, which is the pre-existing behaviour.
     */
    private function gameImage($game): ?GameImage
    {
        if ($game instanceof Game) {
            return $game->images;
        }

        return PackshotJoin::hydrate($game);
    }
}
