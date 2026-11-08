<?php

namespace App\Domain\Game;

use App\Models\GameImage;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Sets ImageResolver::CACHE_CONTROL on packshot objects that are already in the bucket.
 *
 * New uploads get the header from ImageResolver::uploadOptions(). Objects uploaded before that
 * have none, so the CDN serves them with its one-hour default. S3 can't change an object's
 * metadata on its own: the object is copied onto itself with MetadataDirective REPLACE, which
 * leaves the image bytes alone but replaces every piece of metadata. So the copy has to restate
 * the Content-Type (read from the object first), any user metadata, and public-read - left out,
 * the object would lose its type and could stop being public. Tested on prod with a test object
 * on 2026-11-08: still public, still image/jpeg, new header served by the CDN.
 *
 * Objects already carrying the header are skipped, so a run can be stopped and started again.
 */
class PackshotCacheControl
{
    const RESULT_UPDATED = 'updated';
    const RESULT_WOULD_UPDATE = 'would update';
    const RESULT_ALREADY_SET = 'already set';
    const RESULT_MISSING = 'missing';
    const RESULT_NO_CONTENT_TYPE = 'no content type';

    private ?S3Client $client = null;

    public function __construct(private ImageResolver $resolver)
    {
    }

    /**
     * For tests, which mock the S3 client.
     */
    public function useClient(S3Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Every packshot key that game_images says is in the bucket, in game_images id order.
     *
     * @return \Generator<string>
     */
    public function keys(): \Generator
    {
        $rows = DB::table('game_images')
            ->join('games', 'games.id', '=', 'game_images.game_id')
            ->where('game_images.location', GameImage::LOCATION_SPACES)
            ->select(
                'game_images.id', 'games.console_id',
                'game_images.square_filename', 'game_images.header_filename'
            )
            ->lazyById(500, 'game_images.id', 'id');

        foreach ($rows as $row) {
            if ($row->square_filename) {
                yield $this->resolver->storageKey($row, ImageResolver::TYPE_SQUARE, $row->square_filename);
            }
            if ($row->header_filename) {
                yield $this->resolver->storageKey($row, ImageResolver::TYPE_HEADER, $row->header_filename);
            }
        }
    }

    /**
     * Check one object and, if $apply, set the header on it.
     *
     * @return string one of the RESULT_ constants
     * @throws S3Exception for anything other than a missing object
     */
    public function update(string $key, bool $apply): string
    {
        $bucket = $this->bucket();

        try {
            $head = $this->client()->headObject(['Bucket' => $bucket, 'Key' => $key]);
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404) {
                return self::RESULT_MISSING;
            }
            throw $e;
        }

        if (($head['CacheControl'] ?? null) === ImageResolver::CACHE_CONTROL) {
            return self::RESULT_ALREADY_SET;
        }

        // Every upload sets a Content-Type, so a missing one is unexpected. Leave the object
        // alone rather than guess, as REPLACE would otherwise save it with no type at all.
        if (empty($head['ContentType'])) {
            return self::RESULT_NO_CONTENT_TYPE;
        }

        if (!$apply) {
            return self::RESULT_WOULD_UPDATE;
        }

        $this->client()->copyObject([
            'Bucket' => $bucket,
            'Key' => $key,
            'CopySource' => $bucket.'/'.$this->encodeKey($key),
            'MetadataDirective' => 'REPLACE',
            'CacheControl' => ImageResolver::CACHE_CONTROL,
            'ContentType' => $head['ContentType'],
            'Metadata' => $head['Metadata'] ?? [],
            'ACL' => 'public-read',
        ]);

        return self::RESULT_UPDATED;
    }

    private function client(): S3Client
    {
        return $this->client ??= Storage::disk(ImageResolver::DISK)->getClient();
    }

    private function bucket(): string
    {
        return (string) config('filesystems.disks.packshots.bucket');
    }

    /**
     * CopySource must be URL-encoded; the slashes between segments stay as they are.
     */
    private function encodeKey(string $key): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $key)));
    }
}
