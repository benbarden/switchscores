<?php

namespace Tests\Feature\Staff;

use App\Models\Console;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The game editor's video type field (#36): the options and the stored value it selects.
 */
class GamesEditorVideoTypeTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Video Type Test',
            'email' => 'xx.video.type.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    public function testEditPageShowsTheOptionsAndSelectsTheStoredValue()
    {
        $game = new Game();
        $game->title = 'Zqx Video Type '.uniqid();
        $game->link_title = 'test-video-type-'.uniqid();
        $game->console_id = Console::ID_SWITCH_2;
        $game->video_type = 2;
        $game->save();

        $response = $this->actingAs($this->owner())->get(route('staff.games.edit', ['gameId' => $game->id]));

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '<option value=""></option>',
            '<option value="0">No video</option>',
            '<option value="1">Trailer</option>',
            '<option value="2">Gameplay</option>',
        ], false);
        $response->assertSee('formVals[\'video_type\'] = "2";', false);
    }

    public function testAddPageShowsTheOptions()
    {
        $response = $this->actingAs($this->owner())->get(route('staff.games.add'));

        $response->assertStatus(200);
        $response->assertSee('<option value="1">Trailer</option>', false);
    }
}
