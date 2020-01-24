<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Routing\Controller as Controller;

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

    public function showList($gameId = null)
    {
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Game title hashes';
        $bindings['PageTitle'] = 'Game title hashes';

        if ($gameId) {
            $titleHashList = $serviceGameTitleHash->getByGameId($gameId);
        } else {
            $titleHashList = $serviceGameTitleHash->getAll();
        }
        $jsInitialSort = "[ 0, 'desc']";

        $bindings['TitleHashList'] = $titleHashList;
        $bindings['jsInitialSort'] = $jsInitialSort;

        $bindings['GameId'] = $gameId;

        return view('admin.games-title-hash.list', $bindings);
    }

    public function add()
    {
        $serviceGameTitleHash = $this->getServiceGameTitleHash();
        $serviceGame = $this->getServiceGame();

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('admin.games-title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $titleHash = $serviceGameTitleHash->generateHash($request->title);
            $existingTitleHash = $serviceGameTitleHash->getByHash($titleHash);

            $validator->after(function ($validator) use ($existingTitleHash) {
                // Check for duplicates
                if ($existingTitleHash != null) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('admin.games-title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Add to DB
            $gameId = $request->game_id;
            $gameTitleHash = $serviceGameTitleHash->create($request->title, $titleHash, $gameId);

            // Done
            //return redirect(route('admin.games-title-hash.list', ['gameId' => $gameId]));
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            $urlGameId = \Request::get('gameId');
            if ($urlGameId) {
                $bindings['UrlGameId'] = $urlGameId;
            }
        }

        $bindings['TopTitle'] = 'Admin - Games - Add game title hash';
        $bindings['PageTitle'] = 'Add game title hash';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();

        return view('admin.games-title-hash.add', $bindings);
    }

    public function edit($gameTitleHashId)
    {
        $serviceGameTitleHash = $this->getServiceGameTitleHash();
        $serviceGame = $this->getServiceGame();

        $gameTitleHashData = $serviceGameTitleHash->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $titleHash = $serviceGameTitleHash->generateHash($request->title);

            $gameId = $request->game_id;
            $serviceGameTitleHash->edit($gameTitleHashData, $request->title, $titleHash, $gameId);

            // Done
            //return redirect(route('admin.games-title-hash.list', ['gameId' => $gameId]));
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Games - Edit game title hash';
        $bindings['PageTitle'] = 'Edit game title hash';
        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;
        $bindings['FormMode'] = 'edit';

        $bindings['GamesList'] = $serviceGame->getAll();

        return view('admin.games-title-hash.edit', $bindings);
    }

    public function delete($gameTitleHashId)
    {
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        $gameTitleHashData = $serviceGameTitleHash->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceGameTitleHash->delete($gameTitleHashId);

            // Done

            return redirect(route('admin.games-title-hash.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Games - Delete game title hash';
        $bindings['PageTitle'] = 'Delete game title hash';
        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;

        return view('admin.games-title-hash.delete', $bindings);
    }
}