<?php

namespace Tests\Feature\Staff;

use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * The staff "Update images" button: it reports what the download did instead of
 * always saying OK. The helper is mocked, so nothing is scraped or downloaded.
 */
class RedownloadPackshotsTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Redownload Packshots Test',
            'email' => 'xx.redownload.packshots.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(?string $storeUrlOverride = null): Game
    {
        $game = new Game();
        $game->title = 'Zqx Redownload Packshots '.uniqid();
        $game->link_title = 'test-redownload-packshots-'.uniqid();
        $game->nintendo_store_url_override = $storeUrlOverride;
        $game->save();
        return $game;
    }

    private function helperReturns(string $result, ?string $error = null): void
    {
        $this->mock(DownloadPackshotHelper::class, function (MockInterface $mock) use ($result, $error) {
            $mock->shouldReceive('downloadForGame')->with(\Mockery::type(Game::class), true)->andReturn($result);
            $mock->shouldReceive('lastError')->andReturn($error);
        });
    }

    private function redownload(Game $game)
    {
        return $this->actingAs($this->owner())
            ->getJson(route('staff.games.detail.redownloadPackshots', ['gameId' => $game->id]));
    }

    public function testAFailedDownloadReportsItsError()
    {
        // A helper exception (no square URL found from the header URL) used to come back as OK
        $this->helperReturns(DownloadPackshotHelper::RESULT_FAILED, 'Cannot find square URL match from header URL: x');

        $this->redownload($this->makeGame('https://www.nintendo.com/en-gb/x.html'))
            ->assertStatus(500)
            ->assertJson(['error' => 'Cannot find square URL match from header URL: x']);
    }

    public function testAGameWithOnlyAStoreUrlOverrideIsDownloaded()
    {
        // Used to be refused with "Cannot find NintendoCoUk source data"
        $this->helperReturns(DownloadPackshotHelper::RESULT_DOWNLOADED);

        $this->redownload($this->makeGame('https://www.nintendo.com/en-gb/x.html'))
            ->assertStatus(200)
            ->assertJson(['status' => 'OK']);
    }

    public function testAGameWithNoSourceIsRefused()
    {
        $this->assertSame(
            DownloadPackshotHelper::RESULT_NO_SOURCE,
            app(DownloadPackshotHelper::class)->downloadForGame($this->makeGame(), true)
        );

        $this->redownload($this->makeGame())->assertStatus(400);
    }
}
