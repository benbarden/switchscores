<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotCacheControl;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameImage;
use Aws\Command;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

/**
 * Setting the cache header on existing packshot objects. The copy onto itself replaces all of
 * an object's metadata, so these check that the Content-Type, user metadata and public access
 * are restated, that a dry run never writes, and that objects already done are left alone.
 * S3 is mocked, so nothing is called.
 */
class PackshotCacheControlTest extends TestCase
{
    use DatabaseTransactions;

    private function updater(S3Client $client): PackshotCacheControl
    {
        config(['filesystems.disks.packshots.bucket' => 'test-bucket']);

        return app(PackshotCacheControl::class)->useClient($client);
    }

    private function clientWithHead(array $head): Mockery\MockInterface
    {
        $client = Mockery::mock(S3Client::class);
        $client->shouldReceive('headObject')
            ->with(['Bucket' => 'test-bucket', 'Key' => 'switch-1/header/1-test.jpg'])
            ->andReturn(new Result($head));

        return $client;
    }

    public function test_upload_options_set_public_and_the_cache_header()
    {
        $this->assertSame(
            ['visibility' => 'public', 'CacheControl' => 'public, max-age=31536000, immutable'],
            ImageResolver::uploadOptions()
        );
    }

    public function test_apply_copies_the_object_onto_itself_restating_its_metadata()
    {
        $client = $this->clientWithHead([
            'ContentType' => 'image/png',
            'CacheControl' => null,
            'Metadata' => ['source' => 'nintendo'],
        ]);
        $client->shouldReceive('copyObject')->once()->with([
            'Bucket' => 'test-bucket',
            'Key' => 'switch-1/header/1-test.jpg',
            'CopySource' => 'test-bucket/switch-1/header/1-test.jpg',
            'MetadataDirective' => 'REPLACE',
            'CacheControl' => ImageResolver::CACHE_CONTROL,
            'ContentType' => 'image/png',
            'Metadata' => ['source' => 'nintendo'],
            'ACL' => 'public-read',
        ])->andReturn(new Result([]));

        $result = $this->updater($client)->update('switch-1/header/1-test.jpg', true);

        $this->assertSame(PackshotCacheControl::RESULT_UPDATED, $result);
    }

    public function test_dry_run_never_copies()
    {
        $client = $this->clientWithHead(['ContentType' => 'image/jpeg']);
        $client->shouldNotReceive('copyObject');

        $result = $this->updater($client)->update('switch-1/header/1-test.jpg', false);

        $this->assertSame(PackshotCacheControl::RESULT_WOULD_UPDATE, $result);
    }

    public function test_an_object_that_already_has_the_header_is_skipped()
    {
        $client = $this->clientWithHead([
            'ContentType' => 'image/jpeg',
            'CacheControl' => ImageResolver::CACHE_CONTROL,
        ]);
        $client->shouldNotReceive('copyObject');

        $result = $this->updater($client)->update('switch-1/header/1-test.jpg', true);

        $this->assertSame(PackshotCacheControl::RESULT_ALREADY_SET, $result);
    }

    public function test_an_object_with_no_content_type_is_left_alone()
    {
        $client = $this->clientWithHead(['ContentType' => '']);
        $client->shouldNotReceive('copyObject');

        $result = $this->updater($client)->update('switch-1/header/1-test.jpg', true);

        $this->assertSame(PackshotCacheControl::RESULT_NO_CONTENT_TYPE, $result);
    }

    public function test_a_missing_object_is_reported_not_thrown()
    {
        $client = Mockery::mock(S3Client::class);
        $client->shouldReceive('headObject')->andThrow(new S3Exception(
            'Not Found', new Command('HeadObject'), ['response' => new Response(404), 'code' => 'NotFound']
        ));
        $client->shouldNotReceive('copyObject');

        $result = $this->updater($client)->update('switch-1/header/1-test.jpg', true);

        $this->assertSame(PackshotCacheControl::RESULT_MISSING, $result);
    }

    public function test_other_errors_are_thrown()
    {
        $client = Mockery::mock(S3Client::class);
        $client->shouldReceive('headObject')->andThrow(new S3Exception(
            'Forbidden', new Command('HeadObject'), ['response' => new Response(403), 'code' => 'AccessDenied']
        ));

        $this->expectException(S3Exception::class);

        $this->updater($client)->update('switch-1/header/1-test.jpg', true);
    }

    public function test_keys_cover_both_packshots_under_the_console_prefix()
    {
        $game = Game::create([
            'title' => 'Packshot Cache Control Test Game',
            'link_title' => 'packshot-cache-control-test',
            'console_id' => Console::ID_SWITCH_2,
            'eu_is_released' => 0,
        ]);
        GameImage::create([
            'game_id' => $game->id,
            'square_filename' => $game->id.'-square.jpg',
            'header_filename' => $game->id.'-header.jpg',
            'location' => GameImage::LOCATION_SPACES,
        ]);

        $keys = iterator_to_array(app(PackshotCacheControl::class)->keys(), false);

        $this->assertContains("switch-2/square/{$game->id}-square.jpg", $keys);
        $this->assertContains("switch-2/header/{$game->id}-header.jpg", $keys);
    }

    public function test_keys_leave_out_games_still_on_legacy_storage()
    {
        $game = Game::create([
            'title' => 'Packshot Cache Control Legacy Game',
            'link_title' => 'packshot-cache-control-legacy',
            'console_id' => Console::ID_SWITCH_1,
            'eu_is_released' => 0,
        ]);
        GameImage::create([
            'game_id' => $game->id,
            'header_filename' => $game->id.'-legacy.jpg',
            'location' => GameImage::LOCATION_LEGACY,
        ]);

        $keys = iterator_to_array(app(PackshotCacheControl::class)->keys(), false);

        $this->assertNotContains("switch-1/header/{$game->id}-legacy.jpg", $keys);
    }
}
