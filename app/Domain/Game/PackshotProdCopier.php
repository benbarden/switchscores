<?php

namespace App\Domain\Game;

use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Localdev only: copies a game's packshots from the public prod CDN into local MinIO (#191).
 *
 * Localdev's game_images rows say every game is in object storage, but local MinIO holds
 * almost none of the objects, so pages under test show no images. This reads the exact file
 * prod serves and writes it under the same key. It never writes to prod and never changes
 * the DB - the game_images row already names the right file.
 */
class PackshotProdCopier
{
    public function __construct(
        private ImageResolver $resolver
    )
    {
    }

    public function isAllowed(): bool
    {
        return app()->environment('local');
    }

    /**
     * Copies each packshot whose object is missing from the local bucket.
     *
     * @return array type => 'copied' | 'exists' | 'not on prod' | 'no filename'
     */
    public function copyMissing(Game $game): array
    {
        if (!$this->isAllowed()) {
            throw new \RuntimeException('Copy from prod is for localdev only.');
        }

        $image = GameImage::where('game_id', $game->id)->first();
        $disk = Storage::disk(ImageResolver::DISK);
        $prodUrl = rtrim((string) config('filesystems.disks.packshots.prod_url'), '/');

        $results = [];
        foreach ([ImageResolver::TYPE_SQUARE, ImageResolver::TYPE_HEADER] as $type) {
            $filename = $image ? ($type === ImageResolver::TYPE_SQUARE ? $image->square_filename : $image->header_filename) : null;
            if (!$filename) {
                $results[$type] = 'no filename';
                continue;
            }

            $key = $this->resolver->storageKey($game, $type, $filename);
            if ($disk->exists($key)) {
                $results[$type] = 'exists';
                continue;
            }

            $response = Http::timeout(15)->get($prodUrl.'/'.$key);
            if (!$response->successful()) {
                $results[$type] = 'not on prod';
                continue;
            }

            $disk->put($key, $response->body(), 'public');
            $results[$type] = 'copied';
        }

        return $results;
    }
}
