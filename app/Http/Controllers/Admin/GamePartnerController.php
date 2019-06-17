<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Auth;

use App\Services\ServiceContainer;
use App\Factories\GameDirectorFactory;
use App\Factories\GameChangeHistoryFactory;
use App\Factories\GamesCompanyFactory;


class GamePartnerController extends Controller
{
    public function showGamePartners($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Partners for game: '.$gameTitle;
        $bindings['PageTitle'] = 'Partners for game: '.$gameTitle;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameDeveloperList'] = $serviceGameDeveloper->getByGame($gameId);
        $bindings['GamePublisherList'] = $serviceGamePublisher->getByGame($gameId);
        $bindings['UnusedPublisherList'] = $serviceGamePublisher->getPublishersNotOnGame($gameId);
        $bindings['UnusedDeveloperList'] = $serviceGameDeveloper->getDevelopersNotOnGame($gameId);

        return view('admin.games.partner.gamePartners', $bindings);
    }

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

    public function saveDevPub()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGame = $serviceContainer->getGameService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        // Expected fields
        //$gameDeveloper = $request->developer;
        //$gamePublisher = $request->publisher;

        // Save details
        $gameOrig = $game->fresh();
        GameDirectorFactory::updateExisting($game, $request->post());

        // Game change history
        $game->refresh();
        GameChangeHistoryFactory::makeHistory($game, $gameOrig, Auth::user()->id, 'games');

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function createNewCompany()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePartner = $serviceContainer->getPartnerService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        // Expected fields
        $name = $request->name;
        $linkTitle = $request->linkTitle;
        if (!$name) {
            return response()->json(['error' => 'Missing data: name'], 400);
        } elseif (!$linkTitle) {
            return response()->json(['error' => 'Missing data: linkTitle'], 400);
        }

        // De-dupe
        $partner = $servicePartner->getByName($name);
        if ($partner) {
            return response()->json(['error' => 'Partner already exists with that name'], 400);
        }
        $partner = $servicePartner->getByLinkTitle($linkTitle);
        if ($partner) {
            return response()->json(['error' => 'Partner already exists with that linkTitle'], 400);
        }

        // OK to proceed
        $partner = GamesCompanyFactory::createActive($name, $linkTitle);
        $partner->save();

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function legacyFixDev()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePartner = $serviceContainer->getPartnerService();
        $serviceUser = $serviceContainer->getUserService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        // Expected fields
        $gameId = $request->gameId;
        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        // Legacy field check
        $legacyDev = $game->developer;
        if (!$legacyDev) {
            return response()->json(['error' => 'Developer field not set'], 400);
        }

        // Get partner
        $partner = $servicePartner->getByName($legacyDev);
        if (!$partner) {
            // Well, let's be helpful and create it!
            $partner = GamesCompanyFactory::createActiveNameOnly($legacyDev);
            $partner->save();
            //return response()->json(['error' => 'Partner does not exist. Create it first.'], 400);
        }
        $partnerId = $partner->id;

        // Check partner association doesn't exist
        if ($serviceGameDeveloper->gameHasDeveloper($gameId, $partnerId)) {
            return response()->json(['error' => 'Partner already assigned to game.'], 400);
        }

        // OK to proceed
        $serviceGameDeveloper->createGameDeveloper($gameId, $partnerId);
        $game->developer = null;
        $game->save();

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function legacyFixPub()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $servicePartner = $serviceContainer->getPartnerService();
        $serviceUser = $serviceContainer->getUserService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        // Expected fields
        $gameId = $request->gameId;
        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        // Legacy field check
        $legacyPub = $game->publisher;
        if (!$legacyPub) {
            return response()->json(['error' => 'Publisher field not set'], 400);
        }

        // Get partner
        $partner = $servicePartner->getByName($legacyPub);
        if (!$partner) {
            // Well, let's be helpful and create it!
            $partner = GamesCompanyFactory::createActiveNameOnly($legacyPub);
            $partner->save();
            //return response()->json(['error' => 'Partner does not exist. Create it first.'], 400);
        }
        $partnerId = $partner->id;

        // Check partner association doesn't exist
        if ($serviceGamePublisher->gameHasPublisher($gameId, $partnerId)) {
            return response()->json(['error' => 'Partner already assigned to game.'], 400);
        }

        // OK to proceed
        $serviceGamePublisher->createGamePublisher($gameId, $partnerId);
        $game->publisher = null;
        $game->save();

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}