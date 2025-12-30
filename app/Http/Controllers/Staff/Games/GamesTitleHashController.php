<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;

class GamesTitleHashController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:150',
        //'title_hash' => 'required|max:64',
        'game_id' => 'required',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private GameTitleHashRepository $repoGameTitleHash,
        private HashGeneratorRepository $gameTitleHashGenerator
    )
    {
    }

    public function showList($gameId = null)
    {
        $pageTitle = 'Game title hashes';
        if ($gameId) {
            $game = $this->repoGame->find($gameId);
            if (!$game) abort(404);
            $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;
        } else {
            $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;
            $titleHashList = $this->repoGameTitleHash->getAll();
        }

        $bindings['TitleHashList'] = $titleHashList;

        $bindings['GameId'] = $gameId;

        return view('staff.games.title-hash.list', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add game title hash';

        $request = request();

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.games.title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check title hash is unique
            $titleLowercase = strtolower($request->title);
            $hashedTitle = $this->gameTitleHashGenerator->generateHash($request->title);
            $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);

            $validator->after(function ($validator) use ($hashExists) {
                // Check for duplicates
                if ($hashExists) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.games.title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Add to DB
            $gameId = $request->game_id;
            $gameTitleHash = $this->repoGameTitleHash->create($titleLowercase, $hashedTitle, $gameId);

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            $urlGameId = \Request::get('gameId');

            if ($urlGameId) {
                $game = $this->repoGame->find($urlGameId);
                if (!$game) abort(404);
                $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;
            } else {
                abort(404);
            }

            if ($urlGameId) {
                $bindings['GameId'] = $urlGameId;
            }
        }

        $bindings['FormMode'] = 'add';

        return view('staff.games.title-hash.add', $bindings);
    }

    public function edit($gameTitleHashId)
    {
        $gameTitleHashData = $this->repoGameTitleHash->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $pageTitle = 'Edit game title hash';

        $gameId = $gameTitleHashData->game_id;

        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $titleLowercase = strtolower($request->title);
            $hashedTitle = $this->gameTitleHashGenerator->generateHash($request->title);

            $gameId = $request->game_id;
            $this->repoGameTitleHash->edit($gameTitleHashData, $titleLowercase, $hashedTitle, $gameId);

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;
        $bindings['FormMode'] = 'edit';
        $bindings['GameId'] = $gameId;

        return view('staff.games.title-hash.edit', $bindings);
    }

    public function delete($gameTitleHashId)
    {
        $gameTitleHashData = $this->repoGameTitleHash->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $pageTitle = 'Delete game title hash';

        $gameId = $gameTitleHashData->game_id;

        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoGameTitleHash->delete($gameTitleHashId);

            // Done

            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;

        return view('staff.games.title-hash.delete', $bindings);
    }
}