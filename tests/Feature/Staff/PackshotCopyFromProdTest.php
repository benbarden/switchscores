<?php

namespace Tests\Feature\Staff;

use App\Domain\Game\ImageResolver;
use App\Domain\Game\PackshotProdCopier;
use App\Models\Console;
use App\Models\Game;
use App\Models\GameImage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Localdev "Copy from prod" for a game's packshots (#191).
 */
class PackshotCopyFromProdTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Packshot Copy Test',
            'email' => 'xx.packshot.copy.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGameInSpaces(): Game
    {
        $game = new Game();
        $game->title = 'Packshot Copy Test Game';
        $game->link_title = 'packshot-copy-test-'.uniqid();
        $game->console_id = Console::ID_SWITCH_1;
        $game->save();

        $image = new GameImage();
        $image->game_id = $game->id;
        $image->square_filename = $game->id.'-packshot-copy-test.jpg';
        $image->header_filename = $game->id.'-packshot-copy-test.jpg';
        $image->location = GameImage::LOCATION_SPACES;
        $image->save();

        return $game;
    }

    private function asLocal(): void
    {
        $this->app['env'] = 'local';
    }

    public function testCopiesMissingObjectsFromProd()
    {
        $this->asLocal();
        Storage::fake(ImageResolver::DISK);
        Http::fake(['*' => Http::response('jpeg-bytes', 200)]);
        $game = $this->makeGameInSpaces();

        $results = app(PackshotProdCopier::class)->copyMissing($game);

        $this->assertSame(['square' => 'copied', 'header' => 'copied'], $results);
        $key = app(ImageResolver::class)->storageKey($game, ImageResolver::TYPE_SQUARE, $game->id.'-packshot-copy-test.jpg');
        Storage::disk(ImageResolver::DISK)->assertExists($key);
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/'.$key));
    }

    public function testSkipsObjectsAlreadyThere()
    {
        $this->asLocal();
        Storage::fake(ImageResolver::DISK);
        Http::fake(['*' => Http::response('jpeg-bytes', 200)]);
        $game = $this->makeGameInSpaces();
        $key = app(ImageResolver::class)->storageKey($game, ImageResolver::TYPE_HEADER, $game->id.'-packshot-copy-test.jpg');
        Storage::disk(ImageResolver::DISK)->put($key, 'already-here');

        $results = app(PackshotProdCopier::class)->copyMissing($game);

        $this->assertSame('copied', $results['square']);
        $this->assertSame('exists', $results['header']);
        $this->assertSame('already-here', Storage::disk(ImageResolver::DISK)->get($key));
    }

    public function testReportsWhenProdHasNoFile()
    {
        $this->asLocal();
        Storage::fake(ImageResolver::DISK);
        Http::fake(['*' => Http::response('', 404)]);
        $game = $this->makeGameInSpaces();
        // Laravel only skips CSRF when the environment is 'testing'
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($this->owner())->post(route('staff.games.images.copyFromProd', ['gameId' => $game->id]));

        $response->assertRedirect(route('staff.games.images.game', ['gameId' => $game->id]));
        $response->assertSessionHas('error');
    }

    public function testRouteIsClosedOutsideLocaldev()
    {
        Http::fake();
        $game = $this->makeGameInSpaces();

        $response = $this->actingAs($this->owner())->post(route('staff.games.images.copyFromProd', ['gameId' => $game->id]));

        $response->assertStatus(404);
        Http::assertNothingSent();
    }
}
