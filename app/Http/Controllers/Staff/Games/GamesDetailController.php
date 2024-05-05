<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Audit\Repository as AuditRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Models\Game;
use App\Services\DataSources\Queries\Differences;
use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;

use App\Traits\SwitchServices;

class GamesDetailController extends Controller
{
    use SwitchServices;

    protected $repoGame;
    protected $repoAudit;
    protected $repoFeaturedGames;
    protected $repoGameStats;

    public function __construct(
        GameRepository $repoGame,
        AuditRepository $repoAudit,
        FeaturedGameRepository $featuredGames,
        GameStatsRepository $repoGameStats
    )
    {
        $this->repoGame = $repoGame;
        $this->repoAudit = $repoAudit;
        $this->repoFeaturedGames = $featuredGames;
        $this->repoGameStats = $repoGameStats;
    }

    public function show($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $pageTitle = $game->title;
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Total rank count
        $bindings['RankMaximum'] = $this->repoGameStats->totalRanked();

        $bindings['LastAction'] = $lastAction = \Request::get('lastaction');

        $lastGameId = \Request::get('lastgameid');
        if ($lastGameId) {
            $lastGame = $this->repoGame->find($lastGameId);
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

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['PriceNintendoCoUkDifferenceCount'] = $priceNintendoCoUkDifferenceCount[0]->count;
        $bindings['PlayersNintendoCoUkDifferenceCount'] = $playersEUNintendoCoUkDifferenceCount[0]->count;

        // Nintendo.co.uk API data
        $bindings['DataSourceNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        // Audit data
        //$gameAuditsCore = $game->audits()->orderBy('id', 'desc')->get();
        $gameAudits = $this->repoAudit->getAggregatedGameAudits($gameId, 10);
        $bindings['GameAuditsCore'] = $gameAudits;

        // Import rules
        $bindings['ImportRulesEshop'] = $this->getServiceGameImportRuleEshop()->getByGameId($gameId);

        return view('staff.games.detail.show', $bindings);
    }

    public function showFullAudit(Game $game)
    {
        $gameId = $game->id;

        $pageTitle = 'Full audit';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesDetailSubpage($pageTitle, $game);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $gameAudits = $this->repoAudit->getAggregatedGameAudits($gameId, 25);
        $bindings['GameAuditsCore'] = $gameAudits;
        $bindings['GameId'] = $gameId;

        return view('staff.games.detail.fullAudit', $bindings);
    }

    public function updateEshopData($gameId)
    {
        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $this->repoGame->find($gameId);
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
        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        $dsItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
        if (!$dsItem) {
            return response()->json(['error' => 'Cannot find NintendoCoUk source data for this game'], 400);
        }

        $downloadPackshotHelper = new DownloadPackshotHelper();
        $downloadPackshotHelper->downloadForGame($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

}