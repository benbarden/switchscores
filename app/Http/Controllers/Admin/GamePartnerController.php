<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

use Auth;

class GamePartnerController extends Controller
{
    public function showGameDevelopers($gameId)
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

        return view('admin.games.developer.gameDevelopers', $bindings);
    }

    public function showGamePublishers($gameId)
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

        return view('admin.games.publisher.gamePublishers', $bindings);
    }

    public function addGameDeveloper()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePartner = $serviceContainer->getPartnerService();
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

        $developerData = $servicePartner->find($developerId);
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

    public function addGamePublisher()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePartner = $serviceContainer->getPartnerService();
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

        $publisherData = $servicePartner->find($publisherId);
        if (!$publisherData) {
            return response()->json(['error' => 'Publisher not found!'], 400);
        }

        $existingGamePublisher = $serviceGamePublisher->gameHasPublisher($gameId, $publisherId);
        if ($existingGamePublisher) {
            return response()->json(['error' => 'Game already has this publisher!'], 400);
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
            return response()->json(['error' => 'Game id mismatch on game publisher record!'], 400);
        }

        $serviceGamePublisher->delete($gamePublisherId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}