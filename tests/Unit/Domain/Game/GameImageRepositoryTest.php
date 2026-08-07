<?php

namespace Tests\Unit\Domain\Game;

use App\Domain\Game\Repository\GameImageRepository;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameImage;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The Game images dashboard reported 100.0% migrated while packshots were being written
 * with no file extension, then climbed to 100.4% on prod. Both readings came from the
 * same fault: the denominator counted games with non-null LEGACY columns while the
 * numerator counted `game_images` rows, so every correctly-migrated game left the
 * denominator and joined the numerator.
 *
 * These tests pin the property that fixes it - every count answers a question about
 * games, over one nested population - because that is the part that will quietly rot
 * again if someone adds a fourth count later.
 *
 * They assert on DELTAS, never absolutes. The counts run over the whole games table and
 * localdev has ~16.9k real rows in it; an absolute assertion would be asserting against
 * whatever happens to be in the database that day. (Same trap PriceStoreTest documents:
 * a test that passes because the table is empty is not testing anything.)
 */
class GameImageRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    private function repo(): GameImageRepository
    {
        return app(GameImageRepository::class);
    }

    private function makeGame(array $overrides = []): Game
    {
        return Game::create(array_merge([
            'title'          => 'Image Repo Test Game',
            'link_title'     => 'image-repo-test-game',
            'console_id'     => Console::ID_SWITCH_2,
            'eu_is_released' => 0,
        ], $overrides));
    }

    private function counts(): array
    {
        $repo = $this->repo();

        return [
            'withImages'    => $repo->countGamesWithImages(),
            'withoutImages' => $repo->countGamesWithoutImages(),
            'inSpaces'      => $repo->countInSpaces(),
            'legacyOnly'    => $repo->countLegacyOnly(),
        ];
    }

    private function delta(array $before, array $after): array
    {
        return [
            'withImages'    => $after['withImages'] - $before['withImages'],
            'withoutImages' => $after['withoutImages'] - $before['withoutImages'],
            'inSpaces'      => $after['inSpaces'] - $before['inSpaces'],
            'legacyOnly'    => $after['legacyOnly'] - $before['legacyOnly'],
        ];
    }

    /**
     * The exact shape that broke the dashboard: a migrated game has null legacy columns,
     * so the old denominator dropped it while the old numerator kept it.
     */
    public function test_a_spaces_only_game_counts_as_having_images()
    {
        $before = $this->counts();

        $game = $this->makeGame();
        GameImage::create([
            'game_id'         => $game->id,
            'square_filename' => $game->id . '-image-repo-test-game.jpg',
            'header_filename' => $game->id . '-image-repo-test-game.jpg',
            'location'        => GameImage::LOCATION_SPACES,
        ]);

        $delta = $this->delta($before, $this->counts());

        $this->assertSame(1, $delta['withImages'], 'a migrated game still has images');
        $this->assertSame(1, $delta['inSpaces']);
        $this->assertSame(0, $delta['legacyOnly']);
        $this->assertSame(0, $delta['withoutImages'], 'it must not read as having no images');
    }

    public function test_a_legacy_only_game_counts_as_legacy_not_migrated()
    {
        $before = $this->counts();

        $this->makeGame([
            'image_square' => 'sq-legacy-test.jpg',
            'image_header' => 'hdr-legacy-test.jpg',
        ]);

        $delta = $this->delta($before, $this->counts());

        $this->assertSame(1, $delta['withImages']);
        $this->assertSame(1, $delta['legacyOnly']);
        $this->assertSame(0, $delta['inSpaces']);
        $this->assertSame(0, $delta['withoutImages']);
    }

    public function test_a_game_with_no_packshot_anywhere_counts_as_without_images()
    {
        $before = $this->counts();

        $this->makeGame();

        $delta = $this->delta($before, $this->counts());

        $this->assertSame(1, $delta['withoutImages']);
        $this->assertSame(0, $delta['withImages']);
        $this->assertSame(0, $delta['inSpaces']);
        $this->assertSame(0, $delta['legacyOnly']);
    }

    /**
     * The migrated count must never be able to exceed the games-with-images count, which
     * is what produced 100.4%. Adding only migrated games has to move both together.
     */
    public function test_migrating_games_cannot_push_the_percentage_above_one_hundred()
    {
        $before = $this->counts();

        foreach (range(1, 3) as $i) {
            $game = $this->makeGame(['link_title' => 'image-repo-test-game-' . $i]);
            GameImage::create([
                'game_id'         => $game->id,
                'square_filename' => $game->id . '-image-repo-test-game.jpg',
                'location'        => GameImage::LOCATION_SPACES,
            ]);
        }

        $after = $this->counts();
        $delta = $this->delta($before, $after);

        $this->assertSame(3, $delta['withImages']);
        $this->assertSame(3, $delta['inSpaces']);
        $this->assertLessThanOrEqual(
            $after['withImages'],
            $after['inSpaces'],
            'the numerator must stay inside the denominator'
        );
    }

    /**
     * A spaces row naming no file is not a migrated image. Counting the row rather than
     * the filename is how an empty record would be reported as done.
     */
    public function test_a_spaces_row_with_no_filenames_is_not_treated_as_migrated()
    {
        $before = $this->counts();

        $game = $this->makeGame([
            'image_square' => 'sq-empty-row-test.jpg',
        ]);
        GameImage::create([
            'game_id'  => $game->id,
            'location' => GameImage::LOCATION_SPACES,
        ]);

        $delta = $this->delta($before, $this->counts());

        $this->assertSame(0, $delta['inSpaces'], 'no filename means no object');
        $this->assertSame(1, $delta['legacyOnly'], 'so it is still work to do');
        $this->assertSame(1, $delta['withImages']);
    }

    /** withImages splits exactly into migrated + legacy-only, with nothing falling between. */
    public function test_the_two_storage_buckets_account_for_every_game_with_images()
    {
        $legacy = $this->makeGame(['image_square' => 'sq-split-test.jpg']);
        $migrated = $this->makeGame(['link_title' => 'image-repo-split-test']);
        GameImage::create([
            'game_id'         => $migrated->id,
            'header_filename' => $migrated->id . '-image-repo-split-test.jpg',
            'location'        => GameImage::LOCATION_SPACES,
        ]);

        $counts = $this->counts();

        $this->assertSame(
            $counts['withImages'],
            $counts['inSpaces'] + $counts['legacyOnly'],
            'every game with an image is either migrated or still legacy'
        );

        $this->assertNotNull($legacy->id);
    }
}
