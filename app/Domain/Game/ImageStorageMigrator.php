<?php

namespace App\Domain\Game;

use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Support\Facades\Storage;

/**
 * Moves a game's packshots between storage locations, reversibly.
 *
 *   migrate($game) — copies the game's legacy files (public/img/ps-{type}) onto the
 *                    `packshots` disk under {console}/{type}/{filename}, then records
 *                    location = spaces in game_images. Both packshots move together
 *                    (the schema has one location per game).
 *   revert($game)  — deletes the bucket objects and sets location back to legacy.
 *                    The legacy files on disk are never touched, so revert is lossless.
 *
 * Legacy files stay in place until the Phase 2 cutover, so a game can be migrated and
 * reverted freely during the POC.
 */
class ImageStorageMigrator
{
    const LEGACY_DIRS = [
        ImageResolver::TYPE_SQUARE => 'img/ps-square',
        ImageResolver::TYPE_HEADER => 'img/ps-header',
    ];

    public function __construct(private ImageResolver $resolver)
    {
    }

    public function migrate(Game $game): void
    {
        $squareFilename = $this->copyToSpaces($game, ImageResolver::TYPE_SQUARE, $game->image_square);
        $headerFilename = $this->copyToSpaces($game, ImageResolver::TYPE_HEADER, $game->image_header);

        GameImage::updateOrCreate(
            ['game_id' => $game->id],
            [
                'square_filename'   => $squareFilename,
                'header_filename'   => $headerFilename,
                'location'          => GameImage::LOCATION_SPACES,
                'square_updated_at' => $squareFilename ? now() : null,
                'header_updated_at' => $headerFilename ? now() : null,
            ]
        );
    }

    public function revert(Game $game): void
    {
        $image = $game->images;
        if (!$image) {
            return;
        }

        $this->deleteFromSpaces($game, ImageResolver::TYPE_SQUARE, $image->square_filename);
        $this->deleteFromSpaces($game, ImageResolver::TYPE_HEADER, $image->header_filename);

        $image->location = GameImage::LOCATION_LEGACY;
        $image->save();
    }

    /**
     * Copy one legacy packshot onto the packshots disk. Returns the stored filename
     * (prefix stripped, per the {gameId}-{slug}.ext convention) or null if there was
     * no legacy file to copy.
     */
    private function copyToSpaces(Game $game, string $type, ?string $legacyFilename): ?string
    {
        if (!$legacyFilename) {
            return null;
        }

        $sourcePath = public_path(self::LEGACY_DIRS[$type] . '/' . $legacyFilename);
        if (!file_exists($sourcePath)) {
            return null;
        }

        $filename = $this->targetFilename($legacyFilename);
        $key = $this->resolver->storageKey($game, $type, $filename);

        Storage::disk(ImageResolver::DISK)->put($key, file_get_contents($sourcePath));

        return $filename;
    }

    private function deleteFromSpaces(Game $game, string $type, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        Storage::disk(ImageResolver::DISK)->delete($this->resolver->storageKey($game, $type, $filename));
    }

    /**
     * Strip the legacy type prefix (sq- / hdr-); the bucket key already encodes type,
     * leaving the agreed {gameId}-{slug}.ext filename.
     */
    private function targetFilename(string $legacyFilename): string
    {
        return preg_replace('/^(sq|hdr)-/', '', $legacyFilename);
    }
}
