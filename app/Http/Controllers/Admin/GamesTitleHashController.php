<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;


class GamesTitleHashController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:15',
        //'title_hash' => 'required|max:64',
        'game_id' => 'required',
    ];

    public function showList($gameId = null)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameTitleHash = $serviceContainer->getGameTitleHashService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Game Title Hashes';
        $bindings['PanelTitle'] = 'Game Title Hashes';

        if ($gameId) {
            $titleHashList = $serviceGameTitleHash->getByGameId($gameId);
        } else {
            $titleHashList = $serviceGameTitleHash->getAll();
        }
        $jsInitialSort = "[ 0, 'desc']";

        $bindings['TitleHashList'] = $titleHashList;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.games-title-hash.list', $bindings);
    }

    public function add()
    {
        $regionCode = \Request::get('regionCode');

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameTitleHash = $serviceContainer->getGameTitleHashService();
        $serviceGame = $serviceContainer->getGameService();

        $request = request();

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
            $gameTitleHash = $serviceGameTitleHash->create($request->title, $titleHash, $request->game_id);

            // Done
            return redirect(route('admin.games-title-hash.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Games - Add game title hash';
        $bindings['PanelTitle'] = 'Add game title hash';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll($regionCode);

        return view('admin.games-title-hash.add', $bindings);
    }

    public function edit($gameTitleHashId)
    {
        $regionCode = \Request::get('regionCode');

        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameTitleHash = $serviceContainer->getGameTitleHashService();
        $serviceGame = $serviceContainer->getGameService();

        $gameTitleHashData = $serviceGameTitleHash->find($gameTitleHashId);
        if (!$gameTitleHashData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $titleHash = $serviceGameTitleHash->generateHash($request->title);

            $serviceGameTitleHash->edit($gameTitleHashData, $request->title, $titleHash, $request->game_id);

            // Done

            return redirect(route('admin.games-title-hash.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Games - Edit game title hash';
        $bindings['PanelTitle'] = 'Edit game title hash';
        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;
        $bindings['FormMode'] = 'edit';

        $bindings['GamesList'] = $serviceGame->getAll($regionCode);

        return view('admin.games-title-hash.edit', $bindings);
    }

    public function delete($gameTitleHashId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameTitleHash = $serviceContainer->getGameTitleHashService();

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
        $bindings['PanelTitle'] = 'Delete game title hash';
        $bindings['GameTitleHashData'] = $gameTitleHashData;
        $bindings['GameTitleHashId'] = $gameTitleHashId;

        return view('admin.games-title-hash.delete', $bindings);
    }
}