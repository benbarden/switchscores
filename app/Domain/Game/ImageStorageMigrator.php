<?php

namespace App\Domain\Game;

use App\Exceptions\Game\MissingLegacyImage;
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

    /**
     * @throws MissingLegacyImage if the DB names a packshot whose file isn't on disk.
     */
    public function migrate(Game $game): void
    {
        $this->assertLegacyFilesPresent($game);

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
     * Refuse to migrate a game whose DB names a packshot that isn't on disk.
     *
     * Without this the copy just returns null, the row still records location = spaces,
     * and the resolver falls back to the legacy file — so the page looks correct and the
     * image was never actually migrated. That stays invisible until the Phase 2 legacy
     * delete turns it into a broken image. Checked up front so a part-copied game can't
     * be recorded as migrated.
     *
     * A game with no filename recorded at all is legitimate — it simply has no packshot.
     *
     * @throws MissingLegacyImage
     */
    private function assertLegacyFilesPresent(Game $game): void
    {
        $legacyFilenames = [
            ImageResolver::TYPE_SQUARE => $game->image_square,
            ImageResolver::TYPE_HEADER => $game->image_header,
        ];

        foreach ($legacyFilenames as $type => $legacyFilename) {
            if (!$legacyFilename) {
                continue;
            }

            if (!file_exists(public_path(self::LEGACY_DIRS[$type] . '/' . $legacyFilename))) {
                throw new MissingLegacyImage(
                    "Game {$game->id} ({$game->link_title}): {$type} image \"{$legacyFilename}\" is not on disk."
                );
            }
        }
    }

    /**
     * Copy one legacy packshot onto the packshots disk. Returns the stored filename
     * (per the {gameId}-{slug}.ext convention) or null if there was no legacy file
     * to copy.
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

        $filename = $this->resolver->targetFilename($game, $legacyFilename);
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

}
