<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;

use App\Traits\SwitchServices;

class GamesTitleHashController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:150',
        //'title_hash' => 'required|max:64',
        'game_id' => 'required',
    ];

    private $repoGame;

    public function __construct(
        GameRepository $repoGame
    )
    {
        $this->repoGame = $repoGame;
    }

    public function showList($gameId = null)
    {
        $pageTitle = 'Game title hashes';
        if ($gameId) {
            $game = $this->repoGame->find($gameId);
            if (!$game) abort(404);
            $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
            $titleHashList = $this->getServiceGameTitleHash()->getByGameId($gameId);
        } else {
            $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
            $titleHashList = $this->getServiceGameTitleHash()->getAll();
        }
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['TitleHashList'] = $titleHashList;

        $bindings['GameId'] = $gameId;

        return view('staff.games.title-hash.list', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add game title hash';

        $urlGameId = \Request::get('gameId');

        if ($urlGameId) {
            $game = $this->repoGame->find($urlGameId);
            if (!$game) abort(404);
            $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
        } else {
            $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        }
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.games-title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $titleLowercase = strtolower($request->title);
            $hashedTitle = $this->getServiceGameTitleHash()->generateHash($request->title);
            $existingTitleHash = $this->getServiceGameTitleHash()->getByHash($hashedTitle);

            $validator->after(function ($validator) use ($existingTitleHash) {
                // Check for duplicates
                if ($existingTitleHash != null) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.games-title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Add to DB
            $gameId = $request->game_id;
            $gameTitleHash = $this->getServiceGameTitleHash()->create($titleLowercase, $hashedTitle, $gameId);

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            if ($urlGameId) {
                $bindings['UrlGameId'] = $urlGameId;
            }
        }

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        return view('staff.games.title-hash.add', $bindings);
    }

    public function edit($gameTitleHashId)
    {
        $gameTitleHashData = $this->getServiceGameTitleHash()->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $pageTitle = 'Edit game title hash';

        $gameId = $gameTitleHashData->game_id;

        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $titleLowercase = strtolower($request->title);
            $hashedTitle = $this->getServiceGameTitleHash()->generateHash($request->title);

            $gameId = $request->game_id;
            $this->getServiceGameTitleHash()->edit($gameTitleHashData, $titleLowercase, $hashedTitle, $gameId);

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;
        $bindings['FormMode'] = 'edit';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        return view('staff.games.title-hash.edit', $bindings);
    }

    public function delete($gameTitleHashId)
    {
        $gameTitleHashData = $this->getServiceGameTitleHash()->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $pageTitle = 'Delete game title hash';

        $gameId = $gameTitleHashData->game_id;

        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServiceGameTitleHash()->delete($gameTitleHashId);

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