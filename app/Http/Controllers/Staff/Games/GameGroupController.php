<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameGroup\LinkException;
use App\Domain\GameGroup\Linker;
use App\Domain\GameGroup\Repository as GameGroupRepository;

/**
 * Link and unlink the other console version of a game (#44). Staff game detail shows the
 * links read-only; editing happens on this sub-page.
 */
class GameGroupController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameGroupRepository $repoGameGroup,
        private Linker $linker
    )
    {
    }

    public function edit($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $pageTitle = 'Other console versions: '.$game->title;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;

        $gameGroup = $game->gameGroup;
        $bindings['GameData'] = $game;
        $bindings['GameId'] = $game->id;
        $bindings['GameGroup'] = $gameGroup;
        $bindings['GameGroupOthers'] = $gameGroup ? $gameGroup->games->where('id', '!=', $game->id) : collect();

        return view('staff.games.game-group.edit', $bindings);
    }

    public function link($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $request = request();
        $other = $this->repoGame->find($request->input('other_game_id'));
        if (!$other) {
            return $this->backToEdit($gameId, 'Choose a game to link to.');
        }

        try {
            $this->linker->link($game, $other, (string) $request->input('match_type'));
        } catch (LinkException $e) {
            return $this->backToEdit($gameId, $e->getMessage());
        }

        $this->repoGame->clearCacheCoreData($game->id);
        $this->repoGame->clearCacheCoreData($other->id);

        return $this->backToGame($gameId);
    }

    public function unlink($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $group = $game->gameGroup;
        if ($group) {
            $memberIds = $group->games->pluck('id');
            $this->repoGameGroup->unlinkGame($game);
            foreach ($memberIds as $memberId) {
                $this->repoGame->clearCacheCoreData($memberId);
            }
        }

        return $this->backToGame($gameId);
    }

    private function backToEdit($gameId, string $error)
    {
        return redirect()->route('staff.games.gameGroup.edit', ['gameId' => $gameId])->with('gameGroupError', $error);
    }

    private function backToGame($gameId)
    {
        return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);
    }
}
