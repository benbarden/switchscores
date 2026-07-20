<?php


namespace App\Services\Game;

use App\Domain\Game\ImageResolver;
use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Support\Facades\Storage;


class Images
{
    const PATH_IMAGE_SQUARE = '/img/ps-square/';
    const PATH_IMAGE_HEADER = '/img/ps-header/';

    /**
     * @var Game
     */
    private $game;

    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function deleteSquare()
    {
        return $this->delete(ImageResolver::TYPE_SQUARE);
    }

    public function deleteHeader()
    {
        return $this->delete(ImageResolver::TYPE_HEADER);
    }

    /**
     * Delete a packshot from wherever it actually lives.
     *
     * Previously this only ever unlink()ed under public/img. Once ingestion can write to object
     * storage that leaves the real file behind: the caller believes the packshot is gone, the
     * game_images row still names it, and the resolver keeps serving it - so "remove this image"
     * silently does nothing. The clearest symptom would have been re-linking a game to a
     * different API item (GamesEditorController) and still seeing the old game's artwork.
     *
     * Both sides are cleared for a spaces-hosted game: the bucket object and the filename on the
     * row. The row itself is kept (one row per game, unique on game_id) so the location survives
     * for the remaining type.
     */
    private function delete(string $type)
    {
        $image = $this->game->images;

        if ($image && $image->location === GameImage::LOCATION_SPACES) {
            return $this->deleteFromSpaces($image, $type);
        }

        return $this->deleteFromLegacy($type);
    }

    private function deleteFromSpaces(GameImage $image, string $type)
    {
        $column = $type === ImageResolver::TYPE_SQUARE ? 'square_filename' : 'header_filename';
        $filename = $image->$column;

        if (!$filename) return false;

        $resolver = app(ImageResolver::class);
        Storage::disk(ImageResolver::DISK)
            ->delete($resolver->storageKey($this->game, $type, $filename));

        $image->$column = null;
        $image->save();
    }

    private function deleteFromLegacy(string $type)
    {
        $filePath = public_path() . $this->legacyPath($type);
        $fileName = $type === ImageResolver::TYPE_SQUARE
            ? $this->game->image_square
            : $this->game->image_header;

        if (!$fileName) return false;

        $fileToDelete = $filePath . $fileName;
        if (file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }
    }

    private function legacyPath(string $type): string
    {
        return $type === ImageResolver::TYPE_SQUARE
            ? self::PATH_IMAGE_SQUARE
            : self::PATH_IMAGE_HEADER;
    }
}
