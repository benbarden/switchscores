<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

use Auth;

class PublisherController extends Controller
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

        $servicePublisher = $serviceContainer->getPublisherService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Publishers';
        $bindings['PageTitle'] = 'Publishers';

        $bindings['PublisherList'] = $servicePublisher->getAll();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('admin.publisher.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $request = request();

        $servicePublisher = $serviceContainer->getPublisherService();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $publisher = $servicePublisher->create(
                $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.publisher.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Add publisher';
        $bindings['PageTitle'] = 'Add publisher';
        $bindings['FormMode'] = 'add';

        return view('admin.publisher.add', $bindings);
    }

    public function edit($publisherId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePublisher = $serviceContainer->getPublisherService();

        $publisherData = $servicePublisher->find($publisherId);
        if (!$publisherData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $servicePublisher->edit(
                $publisherData, $request->name, $request->link_title, $request->website_url, $request->twitter_id
            );

            return redirect(route('admin.publisher.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit publisher';
        $bindings['PageTitle'] = 'Edit publisher';
        $bindings['PublisherData'] = $publisherData;
        $bindings['PublisherId'] = $publisherId;

        return view('admin.publisher.edit', $bindings);
    }

    public function delete($publisherId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $servicePublisher = $serviceContainer->getPublisherService();

        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $publisherData = $servicePublisher->find($publisherId);
        if (!$publisherData) abort(404);

        $bindings = [];
        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gamePublishers = $serviceGamePublisher->getByPublisherId($publisherId);
        if (count($gamePublishers) > 0) {
            $customErrors[] = 'Game is linked to '.count($gamePublishers).' publisher(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $servicePublisher->delete($publisherId);

            return redirect(route('admin.publisher.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Admin - Delete publisher';
        $bindings['PageTitle'] = 'Delete publisher';
        $bindings['PublisherData'] = $publisherData;
        $bindings['PublisherId'] = $publisherId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('admin.publisher.delete', $bindings);
    }

    public function showGameList($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Publishers for game: '.$gameTitle;
        $bindings['PageTitle'] = 'Publishers for game: '.$gameTitle;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GamePublisherList'] = $serviceGamePublisher->getByGame($gameId);

        $bindings['UnusedPublisherList'] = $serviceGamePublisher->getPublishersNotOnGame($gameId);

        return view('admin.publisher.gamePublishers', $bindings);
    }

    public function addGamePublisher()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePublisher = $serviceContainer->getPublisherService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $publisherId = $request->publisherId;
        if (!$publisherId) {
            return response()->json(['error' => 'Missing data: publisherId'], 400);
        }

        $publisherData = $servicePublisher->find($publisherId);
        if (!$publisherData) {
            return response()->json(['error' => 'Publisher not found!'], 400);
        }

        $existingGamePublisher = $serviceGamePublisher->gameHasPublisher($gameId, $publisherId);
        if ($existingGamePublisher) {
            return response()->json(['error' => 'Game already has this Publisher!'], 400);
        }

        $serviceGamePublisher->createGamePublisher($gameId, $publisherId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGamePublisher()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $gamePublisherId = $request->gamePublisherId;
        if (!$gamePublisherId) {
            return response()->json(['error' => 'Missing data: gamePublisherId'], 400);
        }

        $gamePublisherData = $serviceGamePublisher->find($gamePublisherId);
        if (!$gamePublisherData) {
            return response()->json(['error' => 'Game publisher not found!'], 400);
        }

        if ($gamePublisherData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game Publisher record!'], 400);
        }

        $serviceGamePublisher->delete($gamePublisherId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
