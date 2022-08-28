<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Factories\GameDirectorFactory;
use App\Factories\GamesCompanyFactory;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class GamesPartnerController extends Controller
{
    use SwitchServices;
    use AuthUser;

    public function showGamePartners($gameId)
    {
        $serviceGame = $this->getServiceGame();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Partners for game: '.$gameTitle;
        $bindings['PageTitle'] = 'Partners for game: '.$gameTitle;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameDeveloperList'] = $serviceGameDeveloper->getByGame($gameId);
        $bindings['GamePublisherList'] = $serviceGamePublisher->getByGame($gameId);
        //$bindings['UnusedPublisherList'] = $serviceGamePublisher->getPublishersNotOnGame($gameId);
        //$bindings['UnusedDeveloperList'] = $serviceGameDeveloper->getDevelopersNotOnGame($gameId);

        $bindings['DataSourceNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        return view('staff.games.partner.gamePartners', $bindings);
    }

    public function addGameDeveloper()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $servicePartner = $this->getServicePartner();
        $serviceUser = $this->getServiceUser();

        $userId = $this->getAuthId();

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
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceUser = $this->getServiceUser();

        $userId = $this->getAuthId();

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
        $serviceGamePublisher = $this->getServiceGamePublisher();
        $servicePartner = $this->getServicePartner();
        $serviceUser = $this->getServiceUser();

        $userId = $this->getAuthId();

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
        $serviceGamePublisher = $this->getServiceGamePublisher();
        $serviceUser = $this->getServiceUser();

        $userId = $this->getAuthId();

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

    public function createNewCompany()
    {
        $servicePartner = $this->getServicePartner();
        $serviceUser = $this->getServiceUser();

        $userId = $this->getAuthId();

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

}