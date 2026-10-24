<?php

namespace Tests\Feature\Staff;

use App\Models\Console;
use App\Models\Game;
use App\Models\GameGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Other console versions (#44): read-only on staff game detail, edited on a sub-page.
 */
class GameGroupLinkTest extends TestCase
{
    use DatabaseTransactions;

    private function owner(): User
    {
        return new User([
            'display_name' => 'Game Group Test',
            'email' => 'xx.game.group.test.'.uniqid().'@switchscores.com',
            'is_staff' => 1,
            'is_owner' => 1,
        ]);
    }

    private function makeGame(string $title, int $consoleId): Game
    {
        $game = new Game();
        $game->title = $title;
        $game->link_title = 'test-game-group-link-'.uniqid();
        $game->console_id = $consoleId;
        $game->save();
        return $game;
    }

    private function detailUrl(Game $game): string
    {
        return '/staff/games/detail/'.$game->id.'?lastaction=edit&lastgameid='.$game->id;
    }

    public function testDetailPageIsReadOnlyAndLinksToTheSubPage()
    {
        $game = $this->makeGame('Group Link Test Game', Console::ID_SWITCH_1);

        $response = $this->actingAs($this->owner())->get(route('staff.games.detail', ['gameId' => $game->id]));

        $response->assertStatus(200);
        $response->assertSee('Not linked.');
        $response->assertSee(route('staff.games.gameGroup.edit', ['gameId' => $game->id]), false);
        $response->assertDontSee('game-group-other', false);
    }

    public function testSubPageShowsTheLinkForm()
    {
        $game = $this->makeGame('Group Link Test Game', Console::ID_SWITCH_1);

        $response = $this->actingAs($this->owner())->get(route('staff.games.gameGroup.edit', ['gameId' => $game->id]));

        $response->assertStatus(200);
        $response->assertSee('game-group-other', false);
    }

    public function testLinkThenUnlink()
    {
        $switch1 = $this->makeGame('Group Link Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Group Link Test Game (Switch 2)', Console::ID_SWITCH_2);
        $owner = $this->owner();

        $response = $this->actingAs($owner)->post(
            route('staff.games.gameGroup.link', ['gameId' => $switch2->id]),
            ['other_game_id' => $switch1->id, 'match_type' => GameGroup::MATCH_IDENTICAL]
        );
        $response->assertRedirect($this->detailUrl($switch2));

        $groupId = $switch2->fresh()->game_group_id;
        $this->assertNotNull($groupId);
        $this->assertSame($groupId, $switch1->fresh()->game_group_id);

        // Detail page lists the other version
        $page = $this->actingAs($owner)->get(route('staff.games.detail', ['gameId' => $switch2->id]));
        $page->assertSee(route('staff.games.detail', ['gameId' => $switch1->id]), false);

        // Sub-page offers unlink
        $page = $this->actingAs($owner)->get(route('staff.games.gameGroup.edit', ['gameId' => $switch2->id]));
        $page->assertSee('Unlink this game');

        $response = $this->actingAs($owner)->post(route('staff.games.gameGroup.unlink', ['gameId' => $switch2->id]));
        $response->assertRedirect($this->detailUrl($switch2));
        $this->assertNull($switch1->fresh()->game_group_id);
        $this->assertNull($switch2->fresh()->game_group_id);
    }

    public function testChangeMatchType()
    {
        $switch1 = $this->makeGame('Group Link Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Group Link Test Game (Switch 2)', Console::ID_SWITCH_2);
        $owner = $this->owner();
        $this->actingAs($owner)->post(
            route('staff.games.gameGroup.link', ['gameId' => $switch2->id]),
            ['other_game_id' => $switch1->id, 'match_type' => GameGroup::MATCH_IDENTICAL]
        );

        $response = $this->actingAs($owner)->post(
            route('staff.games.gameGroup.matchType', ['gameId' => $switch1->id]),
            ['match_type' => GameGroup::MATCH_ENHANCED]
        );

        $response->assertRedirect($this->detailUrl($switch1));
        $this->assertSame(GameGroup::MATCH_ENHANCED, $switch2->fresh()->gameGroup->match_type);
    }

    public function testEnhancedLabelNamesEachLinkedGame()
    {
        // #203: from the Switch 2 game, the link is to the original; from the Switch 1 game, to the enhanced edition
        $switch1 = $this->makeGame('Group Link Test Game', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Group Link Test Game (Switch 2)', Console::ID_SWITCH_2);
        $owner = $this->owner();
        $this->actingAs($owner)->post(
            route('staff.games.gameGroup.link', ['gameId' => $switch2->id]),
            ['other_game_id' => $switch1->id, 'match_type' => GameGroup::MATCH_ENHANCED]
        );

        $page = $this->actingAs($owner)->get(route('staff.games.detail', ['gameId' => $switch2->id]));
        $page->assertSee('Original Switch 1 version');
        $page->assertDontSee('Enhanced Switch 2 Edition');

        $page = $this->actingAs($owner)->get(route('staff.games.detail', ['gameId' => $switch1->id]));
        $page->assertSee('Enhanced Switch 2 Edition');
        $page->assertDontSee('Original Switch 1 version');
    }

    public function testGroupsAndSuggestionsPages()
    {
        $switch1 = $this->makeGame('Group Link Test Zq Suggest', Console::ID_SWITCH_1);
        $switch2 = $this->makeGame('Group Link Test Zq Suggest: Nintendo Switch 2 Edition', Console::ID_SWITCH_2);
        $owner = $this->owner();

        $page = $this->actingAs($owner)->get(route('staff.games.gameGroup.suggestions'));
        $page->assertStatus(200);
        $page->assertSee('Group Link Test Zq Suggest: Nintendo Switch 2 Edition');

        $response = $this->actingAs($owner)->post(route('staff.games.gameGroup.suggestions.confirm'), [
            'switch2_id' => $switch2->id, 'switch1_id' => $switch1->id, 'match_type' => GameGroup::MATCH_ENHANCED,
        ]);
        $response->assertRedirect(route('staff.games.gameGroup.suggestions'));
        $response->assertSessionHas('gameGroupSuccess');
        $this->assertSame(GameGroup::MATCH_ENHANCED, $switch2->fresh()->gameGroup->match_type);

        $page = $this->actingAs($owner)->get(route('staff.games.gameGroup.index'));
        $page->assertStatus(200);
        $page->assertSee('Group Link Test Zq Suggest');
    }

    public function testRefusedLinkReturnsToTheSubPageWithTheReason()
    {
        $a = $this->makeGame('Group Link Test A', Console::ID_SWITCH_1);
        $b = $this->makeGame('Group Link Test B', Console::ID_SWITCH_1);

        $response = $this->actingAs($this->owner())->post(
            route('staff.games.gameGroup.link', ['gameId' => $a->id]),
            ['other_game_id' => $b->id, 'match_type' => GameGroup::MATCH_IDENTICAL]
        );

        $response->assertRedirect(route('staff.games.gameGroup.edit', ['gameId' => $a->id]));
        $response->assertSessionHas('gameGroupError');
        $this->assertNull($a->fresh()->game_group_id);
    }
}
