<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameGroup\LinkException;
use App\Domain\GameGroup\Linker;
use App\Domain\GameGroup\Matcher;
use App\Domain\GameGroup\Repository as GameGroupRepository;
use App\Domain\GameGroup\TitleMatchKey;
use App\Models\Console;
use App\Models\GameGroup;

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
        private Linker $linker,
        private Matcher $matcher,
        private TitleMatchKey $titleMatchKey
    )
    {
    }

    /**
     * Every group, those whose titles still differ after normalising first: each difference
     * is either fixed or deliberately kept.
     */
    public function index()
    {
        $pageTitle = 'Game groups';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $rows = $this->repoGameGroup->getAll()->map(function ($group) {
            $switch1 = $group->games->firstWhere('console_id', Console::ID_SWITCH_1);
            $switch2 = $group->games->firstWhere('console_id', Console::ID_SWITCH_2);
            $titlesDiffer = $switch1 && $switch2
                && $this->titleMatchKey->forTitle($switch1->title) !== $this->titleMatchKey->forTitle($switch2->title);
            return ['group' => $group, 'switch1' => $switch1, 'switch2' => $switch2, 'titlesDiffer' => $titlesDiffer];
        })->sortBy(fn ($row) => [!$row['titlesDiffer'], $row['group']->name])->values();

        $bindings['GroupRows'] = $rows;
        $bindings['TitlesDifferCount'] = $rows->where('titlesDiffer', true)->count();

        return view('staff.games.game-group.index', $bindings);
    }

    /**
     * Ungrouped Switch 2 games with one likely Switch 1 match, confirmed one at a time.
     */
    public function suggestions()
    {
        $pageTitle = 'Game group suggestions';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bindings['Suggestions'] = $this->matcher->allPairs();
        $bindings['AmbiguousCount'] = $this->matcher->ambiguousCount();

        return view('staff.games.game-group.suggestions', $bindings);
    }

    public function confirmSuggestion()
    {
        $request = request();
        $switch2 = $this->repoGame->find($request->input('switch2_id'));
        $switch1 = $this->repoGame->find($request->input('switch1_id'));
        $back = redirect()->route('staff.games.gameGroup.suggestions');
        if (!$switch2 || !$switch1) {
            return $back->with('gameGroupError', 'Game not found.');
        }

        try {
            $this->linker->link($switch2, $switch1, (string) $request->input('match_type'));
        } catch (LinkException $e) {
            return $back->with('gameGroupError', $switch2->title.': '.$e->getMessage());
        }

        $this->repoGame->clearCacheCoreData($switch1->id);
        $this->repoGame->clearCacheCoreData($switch2->id);

        return $back->with('gameGroupSuccess', 'Linked '.$switch2->title.' to '.$switch1->title.'.');
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

    public function updateMatchType($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $group = $game->gameGroup;
        $matchType = (string) request()->input('match_type');
        if (!$group || !in_array($matchType, [GameGroup::MATCH_IDENTICAL, GameGroup::MATCH_ENHANCED], true)) {
            return $this->backToEdit($gameId, 'Could not change the match type.');
        }

        $group->match_type = $matchType;
        $group->save();

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
