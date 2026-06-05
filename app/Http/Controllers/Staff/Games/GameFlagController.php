<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameFlag\Repository as GameFlagRepository;

class GameFlagController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private array $validationRules = [
        'flag' => 'required|string|max:100',
        'notes' => 'nullable|string',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameFlagRepository $repoGameFlag,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Games by flag';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bindings['FlagList'] = $this->repoGameFlag->getAllFlagsWithCount();

        return view('staff.games.game-flag.index', $bindings);
    }

    public function edit($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $pageTitle = 'Game flags';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $this->repoGameFlag->add($gameId, $request->post('flag'), $request->post('notes'));

            return redirect(route('staff.games.flag.edit', ['gameId' => $gameId]));
        }

        $bindings['GameFlags'] = $this->repoGameFlag->getByGameId($gameId);
        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;

        return view('staff.games.game-flag.edit', $bindings);
    }

    public function remove($gameId, $flagId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $flag = $this->repoGameFlag->find($flagId);
        if (!$flag || $flag->game_id != $gameId) abort(404);

        $this->repoGameFlag->remove($flagId);

        return redirect(route('staff.games.flag.edit', ['gameId' => $gameId]));
    }
}
