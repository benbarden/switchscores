<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

use Auth;

class DeveloperController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Developers';
        $bindings['PageTitle'] = 'Developers';

        $bindings['DeveloperList'] = $serviceDeveloper->getAll();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('admin.developer.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $developer = $serviceDeveloper->create(
                $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.developer.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Add developer';
        $bindings['PageTitle'] = 'Add developer';
        $bindings['FormMode'] = 'add';

        return view('admin.developer.add', $bindings);
    }

    public function edit($developerId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $developerData = $serviceDeveloper->find($developerId);
        if (!$developerData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceDeveloper->edit(
                $developerData, $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.developer.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit developer';
        $bindings['PageTitle'] = 'Edit developer';
        $bindings['DeveloperData'] = $developerData;
        $bindings['DeveloperId'] = $developerId;

        return view('admin.developer.edit', $bindings);
    }

    public function delete($developerId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $developerData = $serviceDeveloper->find($developerId);
        if (!$developerData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gameDevelopers = $serviceGameDeveloper->getByDeveloperId($developerId);
        if (count($gameDevelopers) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameDevelopers).' developer(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceDeveloper->delete($developerId);

            return redirect(route('admin.developer.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Delete developer';
        $bindings['PageTitle'] = 'Delete developer';
        $bindings['DeveloperData'] = $developerData;
        $bindings['DeveloperId'] = $developerId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.developer.delete', $bindings);
    }

    public function showGameList($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Developers for game: '.$gameTitle;
        $bindings['PageTitle'] = 'Developers for game: '.$gameTitle;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameDeveloperList'] = $serviceGameDeveloper->getByGame($gameId);

        $bindings['UnusedDeveloperList'] = $serviceGameDeveloper->getDevelopersNotOnGame($gameId);

        return view('admin.developer.gameDevelopers', $bindings);
    }

    public function addGameDeveloper()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceDeveloper = $serviceContainer->getDeveloperService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $developerId = $request->developerId;
        if (!$developerId) {
            return response()->json(['error' => 'Missing data: developerId'], 400);
        }

        $developerData = $serviceDeveloper->find($developerId);
        if (!$developerData) {
            return response()->json(['error' => 'Developer not found!'], 400);
        }

        $existingGameDeveloper = $serviceGameDeveloper->gameHasDeveloper($gameId, $developerId);
        if ($existingGameDeveloper) {
            return response()->json(['error' => 'Game already has this developer!'], 400);
        }

        $serviceGameDeveloper->createGameDeveloper($gameId, $developerId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGameDeveloper()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $gameDeveloperId = $request->gameDeveloperId;
        if (!$gameDeveloperId) {
            return response()->json(['error' => 'Missing data: gameDeveloperId'], 400);
        }

        $gameDeveloperData = $serviceGameDeveloper->find($gameDeveloperId);
        if (!$gameDeveloperData) {
            return response()->json(['error' => 'Game developer not found!'], 400);
        }

        if ($gameDeveloperData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game developer record!'], 400);
        }

        $serviceGameDeveloper->delete($gameDeveloperId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}
