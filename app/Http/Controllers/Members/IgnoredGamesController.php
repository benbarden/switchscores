<?php

namespace App\Http\Controllers\Members;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\View\Breadcrumbs\MembersBreadcrumbs;
use App\Domain\View\PageBuilders\MembersPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\UserIgnoredGames\Repository as UserIgnoredGamesRepository;

class IgnoredGamesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private MembersPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private UserIgnoredGamesRepository $repoIgnoredGames
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Hidden games';
        $bindings = $this->pageBuilder->build($pageTitle, MembersBreadcrumbs::membersGenericTopLevel($pageTitle))->bindings;

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['IgnoredGames'] = $this->repoIgnoredGames->byUser($userId);

        return view('members.ignored-games.index', $bindings);
    }

    public function add($gameId)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        // Check if already ignored
        if ($this->repoIgnoredGames->isGameIgnored($userId, $gameId)) {
            return response()->json(['error' => 'Game already hidden'], 400);
        }

        $this->repoIgnoredGames->add($userId, $gameId);

        return response()->json(['status' => 'OK', 'message' => 'Game hidden']);
    }

    public function remove($gameId)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $this->repoIgnoredGames->deleteByUserAndGame($userId, $gameId);

        return response()->json(['status' => 'OK', 'message' => 'Game unhidden']);
    }
}
