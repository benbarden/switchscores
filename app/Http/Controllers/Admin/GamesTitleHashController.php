<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class GamesTitleHashController extends Controller
{
    use SwitchServices;
    use StaffView;

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
        if ($gameId) {
            $bindings = $this->getBindingsGamesDetailSubpage('Game title hashes', $gameId);
            $titleHashList = $this->getServiceGameTitleHash()->getByGameId($gameId);
        } else {
            $bindings = $this->getBindingsGamesSubpage('Game title hashes');
            $titleHashList = $this->getServiceGameTitleHash()->getAll();
        }

        $bindings['TitleHashList'] = $titleHashList;

        $bindings['GameId'] = $gameId;

        return view('admin.games-title-hash.list', $bindings);
    }

    public function add()
    {
        $urlGameId = \Request::get('gameId');

        if ($urlGameId) {
            $bindings = $this->getBindingsGamesDetailSubpage('Add game title hash', $urlGameId);
        } else {
            $bindings = $this->getBindingsGamesTitleHashesSubpage('Add game title hash');
        }

        $request = request();

        if ($request->isMethod('post')) {

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('admin.games-title-hash.add'))
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
                return redirect(route('admin.games-title-hash.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Add to DB
            $gameId = $request->game_id;
            $gameTitleHash = $this->getServiceGameTitleHash()->create($titleLowercase, $hashedTitle, $gameId);

            // Done
            //return redirect(route('admin.games-title-hash.list', ['gameId' => $gameId]));
            return redirect('/staff/games/detail/'.$gameId.'?tabid=title-hashes');

        } else {

            if ($urlGameId) {
                $bindings['UrlGameId'] = $urlGameId;
            }
        }

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        return view('admin.games-title-hash.add', $bindings);
    }

    public function edit($gameTitleHashId)
    {
        $gameTitleHashData = $this->getServiceGameTitleHash()->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $gameId = $gameTitleHashData->game_id;
        $bindings = $this->getBindingsGamesDetailSubpage('Edit game title hash', $gameId);

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

        return view('admin.games-title-hash.edit', $bindings);
    }

    public function delete($gameTitleHashId)
    {
        $gameTitleHashData = $this->getServiceGameTitleHash()->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $gameId = $gameTitleHashData->game_id;
        $bindings = $this->getBindingsGamesDetailSubpage('Delete game title hash', $gameId);

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

        return view('admin.games-title-hash.delete', $bindings);
    }
}