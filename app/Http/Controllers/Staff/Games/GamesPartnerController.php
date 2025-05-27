<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Factories\GamesCompanyFactory;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;

class GamesPartnerController extends Controller
{
    public function __construct(
        private GameRepository $repoGame,
        private GameQualityFilter $gameQualityFilter,
        private GamesCompanyRepository $repoGamesCompany,
        private GamePublisherRepository $repoGamePublisher,
        private GameDeveloperRepository $repoGameDeveloper,
        private DataSourceParsedRepository $repoDataSourceParsed
    )
    {
    }

    public function showGamePartners($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $pageTitle = 'Game partners';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameDeveloperList'] = $this->repoGameDeveloper->byGame($gameId);
        $bindings['GamePublisherList'] = $this->repoGamePublisher->byGame($gameId);

        $bindings['DataSourceNintendoCoUk'] = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);

        return view('staff.games.partner.gamePartners', $bindings);
    }

    public function addGameDeveloper()
    {
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

        $existingGameDeveloper = $this->repoGameDeveloper->gameHasDeveloper($gameId, $developerId);
        if ($existingGameDeveloper) {
            return response()->json(['error' => 'Game already has this developer!'], 400);
        }

        $this->repoGameDeveloper->create($gameId, $developerId);

        $this->gameQualityFilter->updateGame($game, $gamesCompany);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGameDeveloper()
    {
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

        $gameDeveloperData = $this->repoGameDeveloper->find($gameDeveloperId);
        if (!$gameDeveloperData) {
            return response()->json(['error' => 'Game developer not found!'], 400);
        }

        if ($gameDeveloperData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game developer record!'], 400);
        }

        $this->repoGameDeveloper->delete($gameDeveloperId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addGamePublisher()
    {
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

        $existingGamePublisher = $this->repoGamePublisher->gameHasPublisher($gameId, $publisherId);
        if ($existingGamePublisher) {
            return response()->json(['error' => 'Game already has this publisher!'], 400);
        }

        $this->repoGamePublisher->create($gameId, $publisherId);

        $this->gameQualityFilter->updateGame($game, $gamesCompany);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGamePublisher()
    {
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

        $gamePublisherData = $this->repoGamePublisher->find($gamePublisherId);
        if (!$gamePublisherData) {
            return response()->json(['error' => 'Game publisher not found!'], 400);
        }

        if ($gamePublisherData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game publisher record!'], 400);
        }

        $this->repoGamePublisher->delete($gamePublisherId);

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

        // Also add as publisher
        $addToGameAsPublisher = $request->addToGameAsPublisherVal;

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
        $gamesCompanyId = $partner->id;

        // Also add as publisher
        if ($addToGameAsPublisher == "1") {
            $gameId = $request->gameId;
            $game = $this->repoGame->find($gameId);
            $gamesCompany = $this->repoGamesCompany->find($gamesCompanyId);
            if ($gamesCompany) {
                $existingGamePublisher = $this->repoGamePublisher->gameHasPublisher($gameId, $gamesCompanyId);
                if (!$existingGamePublisher) {
                    $this->repoGamePublisher->create($gameId, $gamesCompanyId);
                    $this->gameQualityFilter->updateGame($game, $gamesCompany);
                }
            }

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}