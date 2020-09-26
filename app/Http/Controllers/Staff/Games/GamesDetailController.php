<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Game;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
use App\Services\DataSources\Queries\Differences;

use App\Traits\SwitchServices;

class GamesDetailController extends Controller
{
    use SwitchServices;

    public function show($gameId)
    {
        $serviceGame = $this->getServiceGame();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = $gameTitle.' - Game detail - Staff';
        $bindings['PageTitle'] = $gameTitle;

        $bindings['LastAction'] = $lastAction = \Request::get('lastaction');

        $lastGameId = \Request::get('lastgameid');
        if ($lastGameId) {
            $lastGame = $serviceGame->find($lastGameId);
            if ($lastGame) {
                $bindings['LastGame'] = $lastGame;
            }
        }

        $selectedTabId = \Request::get('tabid');
        $bindings['SelectedTabId'] = $selectedTabId;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameReviews'] = $this->getServiceReviewLink()->getByGame($gameId);
        $bindings['GameQuickReviewList'] = $this->getServiceQuickReview()->getActiveByGame($gameId);
        $bindings['GameDevelopers'] = $this->getServiceGameDeveloper()->getByGame($gameId);
        $bindings['GamePublishers'] = $this->getServiceGamePublisher()->getByGame($gameId);
        $bindings['GameTags'] = $this->getServiceGameTag()->getByGame($gameId);
        $bindings['GameTitleHashes'] = $this->getServiceGameTitleHash()->getByGameId($gameId);

        // Differences
        $dsDifferences = new Differences();
        $dsDifferences->setCountOnly(true);
        $releaseDateEUNintendoCoUkDifferenceCount = $dsDifferences->getReleaseDateEUNintendoCoUk($gameId);
        $priceNintendoCoUkDifferenceCount = $dsDifferences->getPriceNintendoCoUk($gameId);
        $playersEUNintendoCoUkDifferenceCount = $dsDifferences->getPlayersNintendoCoUk($gameId);
        $releaseDateEUWikipediaDifferenceCount = $dsDifferences->getReleaseDateEUWikipedia($gameId);
        $releaseDateUSWikipediaDifferenceCount = $dsDifferences->getReleaseDateUSWikipedia($gameId);
        $releaseDateJPWikipediaDifferenceCount = $dsDifferences->getReleaseDateJPWikipedia($gameId);

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['PriceNintendoCoUkDifferenceCount'] = $priceNintendoCoUkDifferenceCount[0]->count;
        $bindings['PlayersNintendoCoUkDifferenceCount'] = $playersEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['ReleaseDateEUWikipediaDifferenceCount'] = $releaseDateEUWikipediaDifferenceCount[0]->count;
        $bindings['ReleaseDateUSWikipediaDifferenceCount'] = $releaseDateUSWikipediaDifferenceCount[0]->count;
        $bindings['ReleaseDateJPWikipediaDifferenceCount'] = $releaseDateJPWikipediaDifferenceCount[0]->count;

        // Nintendo.co.uk API data
        $dsParsedItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
        if ($dsParsedItem) {
            $bindings['NintendoCoUkDSParsedItem'] = $dsParsedItem;
        }

        // Audit data
        //$gameAuditsCore = $game->audits()->orderBy('id', 'desc')->get();
        $gameAudits = $this->getServiceAudit()->getAggregatedGameAudits($gameId, 10);
        $bindings['GameAuditsCore'] = $gameAudits;

        // Import rules
        $bindings['ImportRulesEshop'] = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);
        $bindings['ImportRulesWikipedia'] = $this->getServiceGameImportRuleWikipedia()->getByGameId($gameId);

        return view('staff.games.detail.show', $bindings);
    }

    public function showFullAudit(Game $game)
    {
        $gameId = $game->id;

        $bindings = [];

        $bindings['TopTitle'] = $game->title.' - Game detail - Staff';
        $bindings['PageTitle'] = $game->title;

        $gameAudits = $this->getServiceAudit()->getAggregatedGameAudits($gameId, 25);
        $bindings['GameAuditsCore'] = $gameAudits;
        $bindings['GameId'] = $gameId;

        return view('staff.games.detail.fullAudit', $bindings);
    }

    public function updateEshopData($gameId)
    {
        $serviceGame = $this->getServiceGame();

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        $dsItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
        if (!$dsItem) {
            return response()->json(['error' => 'Cannot find NintendoCoUk source data for this game'], 400);
        }

        $gameImportRule = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);

        UpdateGameFactory::doUpdate($game, $dsItem, $gameImportRule);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function redownloadPackshots($gameId)
    {
        $serviceGame = $this->getServiceGame();

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        $dsItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
        if (!$dsItem) {
            return response()->json(['error' => 'Cannot find NintendoCoUk source data for this game'], 400);
        }

        DownloadImageFactory::downloadImages($game, $dsItem);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}