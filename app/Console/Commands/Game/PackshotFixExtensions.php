<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotWriter;
use App\Models\GameImage;

/**
 * One-off repair for packshots stored under an extension-less object key.
 *
 * Between the PACKSHOTS_DEFAULT_LOCATION=spaces flip and the PackshotWriter extension fix,
 * any game whose Nintendo source URL carried no file extension went into object storage as a
 * bare `{id}-{slug}` key. The images serve correctly (Flysystem sniffs Content-Type from the
 * bytes, not the key), so nothing is visibly broken - but a later re-download from a URL that
 * DOES carry an extension resolves to a different key, writing a second object and orphaning
 * the first. This renames them to the convention before that can happen.
 *
 * NOT fixable in SQL alone. The object key in the bucket is extension-less too, so updating
 * game_images without moving the object would leave the resolver naming a key that does not
 * exist - a working image turned into a broken one. Copy first, verify, then update the row,
 * then delete the old object.
 *
 * Safe to delete once the affected games are cleared.
 */
class PackshotFixExtensions extends Command
{
    protected $signature = 'packshots:fix-extensions
                            {--game= : Restrict to a single game id}
                            {--apply : Actually make changes (default is a dry run)}';

    protected $description = 'Rename extension-less packshot objects to the {id}-{slug}.ext convention.';

    private bool $apply = false;

    public function handle()
    {
        $this->apply = (bool) $this->option('apply');

        if (!$this->packshotsConfigured()) {
            $this->error('The packshots disk is not configured (PACKSHOTS_* missing). Nothing done.');
            return 1;
        }

        $rows = GameImage::where('location', GameImage::LOCATION_SPACES)
            ->when($this->option('game'), fn ($q) => $q->where('game_id', $this->option('game')))
            ->with('game')
            ->orderBy('game_id')
            ->get();

        $affected = 0;
        $fixed = 0;
        $failed = 0;

        foreach ($rows as $row) {
            foreach ([ImageResolver::TYPE_SQUARE, ImageResolver::TYPE_HEADER] as $type) {
                $column = $type === ImageResolver::TYPE_SQUARE ? 'square_filename' : 'header_filename';
                $filename = $row->$column;

                // Only bare names need repair. pathinfo() also reads a trailing-dot name as
                // extension-less, which is the other shape the defect produced.
                if (!$filename || pathinfo($filename, PATHINFO_EXTENSION) !== '') {
                    continue;
                }

                if (!$row->game) {
                    $this->warn("game_id {$row->game_id}: {$type} - no game record, skipped");
                    continue;
                }

                $affected++;

                $result = $this->repair($row, $row->game, $type, $column, $filename);

                $result ? $fixed++ : $failed++;
            }
        }

        $mode = $this->apply ? 'APPLIED' : 'DRY RUN (re-run with --apply to make changes)';
        $this->line('');
        $this->info("{$mode}: {$affected} extension-less packshot(s) found, {$fixed} ok, {$failed} failed.");

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Copy to the new key, verify it landed, update the row, then delete the old object.
     *
     * That order matters: if anything fails partway, the old object is still in place and the
     * row still points at it, so the image keeps rendering. The reverse order would leave a
     * window where neither key serves.
     */
    private function repair(GameImage $row, $game, string $type, string $column, string $filename): bool
    {
        $oldKey = $this->resolver()->storageKey($game, $type, $filename);
        $disk = Storage::disk(ImageResolver::DISK);

        if (!$disk->exists($oldKey)) {
            $this->error("game {$game->id}: {$type} - object missing at {$oldKey}, skipped");
            return false;
        }

        $extension = $this->extensionFor($disk->get($oldKey));

        if (!$extension) {
            $this->warn("game {$game->id}: {$type} - unrecognised image type, left as is");
            return false;
        }

        $newFilename = "{$filename}.{$extension}";
        $newKey = $this->resolver()->storageKey($game, $type, $newFilename);

        $this->line("game {$game->id} ({$game->title}): {$type}");
        $this->line("  {$oldKey}");
        $this->line("  -> {$newKey}");

        if (!$this->apply) {
            return true;
        }

        $disk->copy($oldKey, $newKey);

        if (!$disk->exists($newKey)) {
            $this->error("game {$game->id}: {$type} - copy did not land, old object left intact");
            return false;
        }

        // The *_updated_at bump is deliberate: the URL changes, so Cloudflare has nothing
        // cached under it, but keeping ?v= consistent with the row costs nothing and keeps
        // the invariant that a write always touches the timestamp.
        $timestampColumn = $type === ImageResolver::TYPE_SQUARE ? 'square_updated_at' : 'header_updated_at';
        $row->$column = $newFilename;
        $row->$timestampColumn = now();
        $row->save();

        $disk->delete($oldKey);

        return true;
    }

    /**
     * Same finfo sniff PackshotWriter uses, against the bytes already in the bucket.
     */
    private function extensionFor(string $contents): ?string
    {
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);

        return PackshotWriter::MIME_EXTENSIONS[$mimeType] ?? null;
    }

    private function resolver(): ImageResolver
    {
        return app(ImageResolver::class);
    }

    private function packshotsConfigured(): bool
    {
        $disk = config('filesystems.disks.packshots');

        return !empty($disk['bucket']) && !empty($disk['key']);
    }
}
