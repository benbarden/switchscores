<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Factories\GameDirectorFactory;
use App\Factories\GamesCompanyFactory;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;

use App\Traits\SwitchServices;

class GamesPartnerController extends Controller
{
    use SwitchServices;

    private $repoGame;
    private $gameQualityFilter;
    private $repoGamesCompany;

    public function __construct(
        GameRepository $repoGame,
        GameQualityFilter $gameQualityFilter,
        GamesCompanyRepository $repoGamesCompany
    )
    {
        $this->repoGame = $repoGame;
        $this->gameQualityFilter = $gameQualityFilter;
        $this->repoGamesCompany = $repoGamesCompany;
    }

    public function showGamePartners($gameId)
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $game = $this->repoGame->find($gameId);
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

        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found!'], 400);
        }

        $developerId = $request->developerId;
        if (!$developerId) {
            return response()->json(['error' => 'Missing data: developerId'], 400);
        }

        $gamesCompany = $this->repoGamesCompany->find($developerId);
        if (!$gamesCompany) {
            return response()->json(['error' => 'Developer not found!'], 400);
        }

        $existingGameDeveloper = $serviceGameDeveloper->gameHasDeveloper($gameId, $developerId);
        if ($existingGameDeveloper) {
            return response()->json(['error' => 'Game already has this developer!'], 400);
        }

        $serviceGameDeveloper->createGameDeveloper($gameId, $developerId);

        $this->gameQualityFilter->updateGame($game, $gamesCompany);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGameDeveloper()
    {
        $serviceGameDeveloper = $this->getServiceGameDeveloper();

        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
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

        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found!'], 400);
        }

        $publisherId = $request->publisherId;
        if (!$publisherId) {
            return response()->json(['error' => 'Missing data: publisherId'], 400);
        }

        $gamesCompany = $this->repoGamesCompany->find($publisherId);
        if (!$gamesCompany) {
            return response()->json(['error' => 'Publisher not found!'], 400);
        }

        $existingGamePublisher = $serviceGamePublisher->gameHasPublisher($gameId, $publisherId);
        if ($existingGamePublisher) {
            return response()->json(['error' => 'Game already has this publisher!'], 400);
        }

        $serviceGamePublisher->createGamePublisher($gameId, $publisherId);

        $this->gameQualityFilter->updateGame($game, $gamesCompany);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGamePublisher()
    {
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
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
        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
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
        $gamesCompany = $this->repoGamesCompany->getByName($name);
        if ($gamesCompany) {
            return response()->json(['error' => 'Partner already exists with that name'], 400);
        }
        $gamesCompany = $this->repoGamesCompany->getByLinkTitle($linkTitle);
        if ($gamesCompany) {
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