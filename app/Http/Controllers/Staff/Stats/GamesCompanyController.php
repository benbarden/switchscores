<?php

namespace App\Http\Controllers\Staff\Stats;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use Auth;

class GamesCompanyController extends Controller
{
    use SiteRequestData;
    use WosServices;

    public function oldDeveloperMultiple()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldDevelopersMultiple();

        $bindings['PageTitle'] = 'Old developers - multiple records';
        $bindings['TopTitle'] = 'Staff - Stats - Old developers - multiple records';

        return view('staff.stats.gamesCompany.old-developer-multiple', $bindings);
    }

    public function oldPublisherMultiple()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldPublishersMultiple();

        $bindings['PageTitle'] = 'Old publishers - multiple records';
        $bindings['TopTitle'] = 'Staff - Stats - Old publishers - multiple records';

        return view('staff.stats.gamesCompany.old-publisher-multiple', $bindings);
    }

    public function oldDeveloperByCount()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldDevelopersByCount();

        $bindings['PageTitle'] = 'Old developers - by count';
        $bindings['TopTitle'] = 'Staff - Stats - Old developers - by count';

        return view('staff.stats.gamesCompany.old-developer-by-count', $bindings);
    }

    public function oldPublisherByCount()
    {
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldPublishersByCount();

        $bindings['PageTitle'] = 'Old publishers - by count';
        $bindings['TopTitle'] = 'Staff - Stats - Old publishers - by count';

        return view('staff.stats.gamesCompany.old-publisher-by-count', $bindings);
    }

    public function oldDeveloperGameList($developer)
    {
        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getByDeveloper($developer);

        $bindings['DeveloperName'] = $developer;
        $developerData = $servicePartner->getByName($developer);
        if ($developerData) {
            $bindings['DeveloperData'] = $developerData;
        }

        $bindings['PageTitle'] = 'Old developers - Game list';
        $bindings['TopTitle'] = 'Staff - Stats - Old developers - Game list';

        return view('staff.stats.gamesCompany.old-developer-game-list', $bindings);
    }

    public function oldPublisherGameList($publisher)
    {
        $serviceGame = $this->getServiceGame();
        $servicePartner = $this->getServicePartner();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getByPublisher($publisher);

        $bindings['PublisherName'] = $publisher;
        $publisherData = $servicePartner->getByName($publisher);
        if ($publisherData) {
            $bindings['PublisherData'] = $publisherData;
        }

        $bindings['PageTitle'] = 'Old publishers - by count';
        $bindings['TopTitle'] = 'Staff - Stats - Old publishers - by count';

        return view('staff.stats.gamesCompany.old-publisher-game-list', $bindings);
    }

    public function clearOldDeveloperField()
    {
        $serviceGame = $this->getServiceGame();
        $serviceUser = $this->getServiceUser();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $serviceGame->clearOldDeveloperField($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function clearOldPublisherField()
    {
        $serviceGame = $this->getServiceGame();
        $serviceUser = $this->getServiceUser();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $serviceGame->clearOldPublisherField($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addAllNewDevelopers()
    {
        $serviceGame = $this->getServiceGame();
        $serviceUser = $this->getServiceUser();
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $developerName = $request->developerName;
        if (!$developerName) {
            return response()->json(['error' => 'Missing data: developerName'], 400);
        }

        $developerData = $servicePartner->getByName($developerName);
        if (!$developerData) {
            return response()->json(['error' => 'No developer record found'], 400);
        }

        $developerId = $developerData->id;

        $gamesWithOldDeveloper = $serviceGame->getByDeveloper($developerName);
        foreach ($gamesWithOldDeveloper as $game) {

            $gameId = $game->id;

            if ($serviceGameDeveloper->gameHasDeveloper($gameId, $developerId)) {
                continue;
            }

            $serviceGameDeveloper->createGameDeveloper($gameId, $developerId);

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeAllOldDevelopers()
    {
        $serviceGame = $this->getServiceGame();
        $serviceUser = $this->getServiceUser();
        $servicePartner = $this->getServicePartner();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $developerName = $request->developerName;
        if (!$developerName) {
            return response()->json(['error' => 'Missing data: developerName'], 400);
        }

        $developerData = $servicePartner->getByName($developerName);
        if (!$developerData) {
            return response()->json(['error' => 'No developer record found'], 400);
        }

        $developerId = $developerData->id;

        $gamesWithOldDeveloper = $serviceGame->getByDeveloper($developerName);
        foreach ($gamesWithOldDeveloper as $game) {

            $gameId = $game->id;

            if (!$serviceGameDeveloper->gameHasDeveloper($gameId, $developerId)) {
                // Failsafe for games that might not have the new record assigned yet
                continue;
            }

            $serviceGame->clearOldDeveloperField($game);

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addAllNewPublishers()
    {
        $serviceGame = $this->getServiceGame();
        $serviceUser = $this->getServiceUser();
        $servicePartner = $this->getServicePartner();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $publisherName = $request->publisherName;
        if (!$publisherName) {
            return response()->json(['error' => 'Missing data: publisherName'], 400);
        }

        $publisherData = $servicePartner->getByName($publisherName);
        if (!$publisherData) {
            return response()->json(['error' => 'No publisher record found'], 400);
        }

        $publisherId = $publisherData->id;

        $gamesWithOldPublisher = $serviceGame->getByPublisher($publisherName);
        foreach ($gamesWithOldPublisher as $game) {

            $gameId = $game->id;

            if (!$serviceGamePublisher->gameHasPublisher($gameId, $publisherId)) {
                $serviceGamePublisher->createGamePublisher($gameId, $publisherId);
            }

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeAllOldPublishers()
    {
        $serviceGame = $this->getServiceGame();
        $serviceUser = $this->getServiceUser();
        $servicePartner = $this->getServicePartner();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $publisherName = $request->publisherName;
        if (!$publisherName) {
            return response()->json(['error' => 'Missing data: publisherName'], 400);
        }

        $publisherData = $servicePartner->getByName($publisherName);
        if (!$publisherData) {
            return response()->json(['error' => 'No publisher record found'], 400);
        }

        $publisherId = $publisherData->id;

        $gamesWithOldPublisher = $serviceGame->getByPublisher($publisherName);
        foreach ($gamesWithOldPublisher as $game) {

            $gameId = $game->id;

            if ($serviceGamePublisher->gameHasPublisher($gameId, $publisherId)) {
                // Failsafe for games that might not have the new record assigned yet
                $serviceGame->clearOldPublisherField($game);
            }

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
