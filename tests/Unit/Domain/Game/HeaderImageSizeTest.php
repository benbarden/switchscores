<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\Game\HeaderImageSize;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameImage;
use App\Models\GameScrapedData;
use App\Services\Game\Images as GameImages;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The crawl commands decide "has Nintendo changed the header image?" by comparing the remote
 * file size against the size of the copy we hold. That comparison used to read
 * public/img/ps-header/{games.image_header} + file_exists() directly, in both GameCrawlBatch
 * and GameCrawlUrl.
 *
 * PackshotWriter leaves games.image_header null when it writes to object storage, so under
 * PACKSHOTS_DEFAULT_LOCATION=spaces such a game reported "no local copy" forever, never matched
 * the remote size, and was re-downloaded on EVERY crawl - silently, with no error, 100 games a
 * night. Same shape as the isEligibleForDownload() trap fixed on 2026-07-20, reached by a
 * different route, and it becomes universal once the Phase 2 legacy delete runs.
 */
class HeaderImageSizeTest extends TestCase
{
    use DatabaseTransactions;

    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'          => 'Header Size Test Game',
            'link_title'     => 'header-size-test-game',
            'console_id'     => Console::ID_SWITCH_2,
            'eu_is_released' => 0,
        ], $overrides));
    }

    private function storeInSpaces(Game $game): void
    {
        GameImage::create([
            'game_id'           => $game->id,
            'header_filename'   => "{$game->id}-header-size-test-game.jpg",
            'location'          => GameImage::LOCATION_SPACES,
            'header_updated_at' => now(),
        ]);
    }

    private function recordScrapedSize(Game $game, int $size): void
    {
        GameScrapedData::create([
            'game_id'           => $game->id,
            'header_image_url'  => 'https://example.test/header.jpg',
            'header_image_size' => $size,
        ]);
    }

    private function writeLegacyFile(Game $game, string $filename, int $bytes): void
    {
        $path = public_path() . GameImages::PATH_IMAGE_HEADER . $filename;
        file_put_contents($path, str_repeat('x', $bytes));
        $this->tempFiles[] = $path;
    }

    private function sizer(): HeaderImageSize
    {
        return app(HeaderImageSize::class);
    }

    /**
     * THE REGRESSION. Header is in object storage with a recorded size. The old code returned
     * null here, so the size never matched the remote and the image was re-downloaded on every
     * single crawl.
     */
    public function test_a_header_in_object_storage_reports_its_recorded_size()
    {
        $game = $this->makeGame();
        $this->storeInSpaces($game);
        $this->recordScrapedSize($game, 147538);

        $this->assertSame(147538, $this->sizer()->current($game));
    }

    /**
     * Legacy games keep working: the file on disk is measured, not merely trusted.
     */
    public function test_a_legacy_header_reports_the_size_of_the_file_on_disk()
    {
        $game = $this->makeGame();
        $filename = "hdr-{$game->id}-header-size-test-game.jpg";
        $this->writeLegacyFile($game, $filename, 1234);

        $game->image_header = $filename;
        $game->save();

        $this->assertSame(1234, $this->sizer()->current($game));
    }

    /**
     * A game we hold nothing for must report null, which reads as "differs from remote" and
     * correctly triggers the download that fetches it.
     */
    public function test_a_game_with_no_header_at_all_reports_null()
    {
        $game = $this->makeGame();

        $this->assertNull($this->sizer()->current($game));
    }

    /**
     * The guard that matters: a recorded size with no actual image stored must NOT be trusted.
     * Otherwise a stale game_scraped_data row could match the remote size and suppress the
     * download forever - the same never-fetches failure the fix is meant to prevent, inverted.
     */
    public function test_a_recorded_size_is_ignored_when_no_header_actually_resolves()
    {
        $game = $this->makeGame();
        $this->recordScrapedSize($game, 147538);

        $this->assertNull($this->sizer()->current($game));
    }

    /**
     * A legacy column naming a file that isn't on disk falls through rather than throwing or
     * reporting a bogus size.
     */
    public function test_a_legacy_column_naming_a_missing_file_reports_null()
    {
        $game = $this->makeGame(['image_header' => 'hdr-does-not-exist.jpg']);

        $this->assertNull($this->sizer()->current($game));
    }
}
