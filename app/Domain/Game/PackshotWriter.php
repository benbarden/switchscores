<?php

namespace App\Domain\Game;

use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Support\Facades\Storage;

/**
 * Single write path for a freshly downloaded packshot.
 *
 * Ingestion downloads to a temp file and then hands it here; this class decides where it
 * lands, based on config('packshots.default_location'):
 *
 *   legacy -> move into public/img/ps-{type}/, set games.image_{type}, no game_images row.
 *             Exactly what ingestion did before object storage.
 *   spaces -> put onto the `packshots` disk under {console}/{type}/{filename} and upsert the
 *             game_images row. games.image_{type} is left alone.
 *
 * Reads go through ImageResolver, which checks game_images first and falls back to the legacy
 * columns, so both shapes render correctly and the setting can be flipped either way without
 * a migration. It governs the NEXT download only, never what is already stored.
 *
 * Naming and bucket layout come from ImageResolver (targetFilename/storageKey), shared with
 * ImageStorageMigrator, so a file that arrives by ingestion is indistinguishable from one that
 * arrived by backfill - and re-downloading a migrated game overwrites its object rather than
 * writing a second one beside it.
 */
class PackshotWriter
{
    const LOCATION_LEGACY = 'legacy';
    const LOCATION_SPACES = 'spaces';

    const LEGACY_PATHS = [
        ImageResolver::TYPE_SQUARE => '/img/ps-square/',
        ImageResolver::TYPE_HEADER => '/img/ps-header/',
    ];

    /**
     * Sniffed MIME type -> the extension we store it under. Deliberately only the formats
     * packshots actually arrive in; anything else is left extension-less rather than guessed at.
     */
    const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    public function __construct(private ImageResolver $resolver)
    {
    }

    public function defaultLocation(): string
    {
        return config('packshots.default_location') === self::LOCATION_SPACES
            ? self::LOCATION_SPACES
            : self::LOCATION_LEGACY;
    }

    /**
     * Store one downloaded packshot for a game.
     *
     * @param Game   $game
     * @param string $type     ImageResolver::TYPE_SQUARE | TYPE_HEADER
     * @param string $tempPath absolute path to the downloaded file
     * @param string $sourceName filename (or URL) the extension is taken from
     *
     * @return string the stored filename
     */
    public function store(Game $game, string $type, string $tempPath, string $sourceName): string
    {
        return $this->defaultLocation() === self::LOCATION_SPACES
            ? $this->storeToSpaces($game, $type, $tempPath, $sourceName)
            : $this->storeToLegacy($game, $type, $tempPath, $sourceName);
    }

    /**
     * Record a legacy file that was already on disk, so the skip-the-download short-circuit
     * still leaves games.image_{type} pointing at it. Without this, a game whose file exists
     * but whose column was cleared would never get the column back.
     */
    public function recordExistingLegacy(Game $game, string $type, string $filename): void
    {
        $column = $this->legacyColumn($type);

        if ($game->$column === $filename) {
            return;
        }

        $game->$column = $filename;
        $game->save();
    }

    private function storeToLegacy(Game $game, string $type, string $tempPath, string $sourceName): string
    {
        $filename = basename($sourceName);
        $destination = public_path() . self::LEGACY_PATHS[$type] . $filename;

        rename($tempPath, $destination);

        $column = $this->legacyColumn($type);
        $game->$column = $filename;
        $game->save();

        return $filename;
    }

    private function storeToSpaces(Game $game, string $type, string $tempPath, string $sourceName): string
    {
        $filename = $this->resolver->targetFilename(
            $game, $sourceName, $this->resolveExtension($tempPath, $sourceName)
        );
        $key = $this->resolver->storageKey($game, $type, $filename);

        Storage::disk(ImageResolver::DISK)->put($key, file_get_contents($tempPath));

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        $this->recordSpacesRow($game, $type, $filename);

        return $filename;
    }

    /**
     * Decide the extension the object is stored under.
     *
     * The source name wins when it has one - it is what the legacy path used, so a game
     * downloaded before and after this change keeps the same key and gets overwritten rather
     * than duplicated. Only when it has none do we sniff the bytes.
     *
     * Sniffing rather than parsing the URL is the point. Nintendo serves packshots from URLs
     * with no extension, with a query string after one, and behind redirects; every one of those
     * shapes defeats pathinfo(), and Images::generateDestFilename() then appends a bare '.' that
     * pathinfo() reads back as no extension at all. The bytes are unambiguous in all three cases.
     *
     * finfo is also exactly what Flysystem uses to set the object's Content-Type
     * (AwsS3V3Adapter::write -> FinfoMimeTypeDetector), so the extension we store and the
     * Content-Type the CDN serves are derived from the same evidence and cannot disagree.
     *
     * Returns null when the type isn't one we recognise, which preserves today's behaviour
     * (extension-less key, correct Content-Type) rather than inventing a wrong extension.
     */
    private function resolveExtension(string $tempPath, string $sourceName): ?string
    {
        $extension = strtolower(pathinfo($sourceName, PATHINFO_EXTENSION));

        if ($extension !== '') {
            return $extension;
        }

        if (!file_exists($tempPath)) {
            return null;
        }

        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($tempPath);

        return self::MIME_EXTENSIONS[$mimeType] ?? null;
    }

    /**
     * Upsert the game_images row one type at a time.
     *
     * Deliberately NOT updateOrCreate() with both filenames: ingestion can legitimately get a
     * header without a square (or replace just one via the override-URL flow), and writing both
     * would null the type that wasn't downloaded. ImageStorageMigrator can write both together
     * because a backfill always has both legacy files in hand; ingestion does not.
     *
     * The *_updated_at bump is load-bearing, not bookkeeping. A re-download keeps the same
     * filename, so the object's URL is unchanged and Cloudflare would keep serving the old
     * image indefinitely. ImageResolver::spacesUrl() appends ?v={updated_at}, so this is the
     * only thing that makes a replaced packshot actually appear - which is the entire point of
     * the override-URL flow.
     */
    private function recordSpacesRow(Game $game, string $type, string $filename): void
    {
        $image = GameImage::firstOrNew(['game_id' => $game->id]);

        if ($type === ImageResolver::TYPE_SQUARE) {
            $image->square_filename = $filename;
            $image->square_updated_at = now();
        } else {
            $image->header_filename = $filename;
            $image->header_updated_at = now();
        }

        $image->location = GameImage::LOCATION_SPACES;
        $image->save();
    }

    private function legacyColumn(string $type): string
    {
        return $type === ImageResolver::TYPE_SQUARE ? 'image_square' : 'image_header';
    }
}
