<?php

namespace Tests\Unit\Domain\DataSource;

use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Models\Console;
use App\Models\DataSourceParsed;
use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * isEligibleForDownload() decides whether a game still needs its packshots fetched. It used to
 * answer from games.image_* plus file_exists() under public/img - both legacy-only signals.
 *
 * A game stored in object storage has null legacy columns and no local file, so once
 * PACKSHOTS_DEFAULT_LOCATION was flipped to `spaces` every such game would have looked
 * permanently eligible: each run re-scraping Nintendo and re-uploading identical images,
 * indefinitely, with no error to notice. This is the regression that guards the flip.
 */
class PackshotEligibilityTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'                 => 'Eligibility Test Game',
            'link_title'            => 'eligibility-test-game',
            'console_id'            => Console::ID_SWITCH_2,
            'eu_is_released'        => 0,
            'eshop_europe_fs_id'    => '70010000099999',
        ], $overrides));
    }

    /**
     * The helper only considers games it has a way to download for, so the game needs a linked
     * data source item to get past the first gate.
     */
    private function linkDataSourceItem(Game $game): void
    {
        DataSourceParsed::create([
            'source_id'  => 2,
            'console_id' => $game->console_id,
            'link_id'    => $game->eshop_europe_fs_id,
            'title'      => $game->title,
            'game_id'    => $game->id,
        ]);
    }

    private function helper(): DownloadPackshotHelper
    {
        return app(DownloadPackshotHelper::class);
    }

    /**
     * The regression. Both packshots are in object storage, so nothing needs downloading -
     * but the legacy columns are null and no local file exists.
     */
    public function test_a_game_stored_in_object_storage_is_not_eligible()
    {
        $game = $this->makeGame();
        $this->linkDataSourceItem($game);

        GameImage::create([
            'game_id'           => $game->id,
            'square_filename'   => "{$game->id}-eligibility-test-game.jpg",
            'header_filename'   => "{$game->id}-eligibility-test-game.jpg",
            'location'          => GameImage::LOCATION_SPACES,
            'square_updated_at' => now(),
            'header_updated_at' => now(),
        ]);

        $this->assertFalse($this->helper()->isEligibleForDownload($game->fresh()));
    }

    /**
     * Object storage but only one packshot recorded - the missing one still needs fetching.
     */
    public function test_a_game_missing_one_packshot_in_object_storage_is_eligible()
    {
        $game = $this->makeGame();
        $this->linkDataSourceItem($game);

        GameImage::create([
            'game_id'           => $game->id,
            'square_filename'   => "{$game->id}-eligibility-test-game.jpg",
            'header_filename'   => null,
            'location'          => GameImage::LOCATION_SPACES,
            'square_updated_at' => now(),
        ]);

        $this->assertTrue($this->helper()->isEligibleForDownload($game->fresh()));
    }

    public function test_a_game_with_no_packshots_at_all_is_eligible()
    {
        $game = $this->makeGame();
        $this->linkDataSourceItem($game);

        $this->assertTrue($this->helper()->isEligibleForDownload($game->fresh()));
    }

    /**
     * Legacy games keep the on-disk check: the column can name a file that isn't there, which
     * is exactly how the missing-header games were found during the backfill.
     */
    public function test_a_legacy_game_whose_file_is_missing_from_disk_is_eligible()
    {
        $game = $this->makeGame([
            'image_square' => 'sq-does-not-exist.jpg',
            'image_header' => 'hdr-does-not-exist.jpg',
        ]);
        $this->linkDataSourceItem($game);

        $this->assertTrue($this->helper()->isEligibleForDownload($game->fresh()));
    }

    /**
     * A game with no data source item and no store override has no way to download, so it is
     * never eligible regardless of what it is missing.
     */
    public function test_a_game_with_no_download_route_is_never_eligible()
    {
        $game = $this->makeGame(['eshop_europe_fs_id' => null]);

        $this->assertFalse($this->helper()->isEligibleForDownload($game->fresh()));
    }
}
