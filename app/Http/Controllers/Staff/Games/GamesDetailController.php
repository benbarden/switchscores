<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Audit\Repository as AuditRepository;
use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Domain\DataSource\NintendoCoUk\GamePriceDecision;
use App\Domain\DataSource\NintendoCoUk\PriceDecision;
use App\Domain\DataSource\NintendoCoUk\Repository as DataSourceRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\GameImportRuleEshop\Repository as GameImportRuleEshopRepository;
use App\Domain\GameFlag\Repository as GameFlagRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\GameTag\Repository as GameTagRepository;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\QuickReview\Repository as QuickReviewRepository;
use App\Domain\ReviewLink\Repository as ReviewLinkRepository;
use App\Domain\Gsc\Snapshot\Repository\GscPageSnapshotRepository;

use App\Enums\GameStatus;
use App\Models\Game;

use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;

use App\Domain\WeeklyBatch\DateDifferences;
use App\Services\DataSources\Queries\Differences;

class GamesDetailController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private AuditRepository $repoAudit,
        private DataSourceRepository $repoDataSource,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameRepository $repoGame,
        private GameDeveloperRepository $repoGameDeveloper,
        private GameImportRuleEshopRepository $repoGameImportRuleEshop,
        private GamePublisherRepository $repoGamePublisher,
        private ReviewLinkRepository $repoReviewLink,
        private GameStatsRepository $repoGameStats,
        private GameTagRepository $repoGameTag,
        private GameTitleHashRepository $repoGameTitleHash,
        private QuickReviewRepository $repoQuickReview,
        private DownloadPackshotHelper $downloadPackshotHelper,
        private GscPageSnapshotRepository $repoGscPageSnapshot,
        private GameFlagRepository $repoGameFlag,
        private GamePriceDecision $gamePriceDecision,
    )
    {
    }

    public function show($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $pageTitle = $game->title;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

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

        $pageAlert = \Request::get('alertmsg');
        if ($pageAlert) {
            $bindings['PageAlert'] = $pageAlert;
        }
        $dsItemId = \Request::get('dsitemid');
        if ($dsItemId) {
            $dsItem = $this->repoDataSource->getParsedItemById($dsItemId);
            if ($dsItem) {
                $bindings['DSItem'] = $dsItem;
            }
        }

        $selectedTabId = \Request::get('tabid');
        $bindings['SelectedTabId'] = $selectedTabId;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameReviews'] = $this->repoReviewLink->byGame($gameId);
        $bindings['GameQuickReviewList'] = $this->repoQuickReview->byGameActive($gameId);
        $bindings['GameDevelopers'] = $this->repoGameDeveloper->byGame($gameId);
        $bindings['GamePublishers'] = $this->repoGamePublisher->byGame($gameId);
        $bindings['GameTags'] = $this->repoGameTag->getGameTags($gameId);
        $bindings['GameTitleHashes'] = $this->repoGameTitleHash->getByGameId($gameId);

        // Snapshot
        $bindings['GscSnapshotData'] = $this->repoGscPageSnapshot->getSnapshotsByGame($gameId, 20);

        // Differences
        $dsDifferences = new Differences();
        $dsDifferences->setCountOnly(true);
        $releaseDateEUNintendoCoUkDifferenceCount = $dsDifferences->getReleaseDateEUNintendoCoUk($gameId);
        $priceNintendoCoUkDifferenceCount = $dsDifferences->getPriceNintendoCoUk($gameId);
        $playersEUNintendoCoUkDifferenceCount = $dsDifferences->getPlayersNintendoCoUk($gameId);

        $bindings['ReleaseDateEUNintendoCoUkDifferenceCount'] = $releaseDateEUNintendoCoUkDifferenceCount[0]->count;
        $bindings['PriceNintendoCoUkDifferenceCount'] = $priceNintendoCoUkDifferenceCount[0]->count;
        $bindings['ReleaseDateWeeklyPasteDifferenceCount'] = count((new DateDifferences())->get($gameId));

        // Switch 2 prices come from the price API (#162), so the search payload's price
        // differs by design for every game ApplyPrices has corrected (#182). Where the
        // price API decides the price, show its decision instead. Where it can't (no
        // standalone SKU, stale, ambiguous, zero), UpdateGames is still the authority,
        // so the scalar check above stands.
        $priceDecision = $this->gamePriceDecision->forGame($game);
        $apiDecidesPrice = $priceDecision && in_array($priceDecision->outcome, [
            PriceDecision::CHANGED, PriceDecision::UNCHANGED, PriceDecision::FROZEN,
        ], true);
        if ($apiDecidesPrice) {
            $bindings['PriceNintendoCoUkDifferenceCount'] = 0;
            $bindings['PriceApiDecision'] = $priceDecision->differs() ? $priceDecision : null;
        }

        $bindings['PlayersNintendoCoUkDifferenceCount'] = $playersEUNintendoCoUkDifferenceCount[0]->count;

        // Nintendo.co.uk API data
        $bindings['DataSourceNintendoCoUk'] = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);

        // Audit data
        //$gameAuditsCore = $game->audits()->orderBy('id', 'desc')->get();
        $gameAudits = $this->repoAudit->getAggregatedGameAudits($gameId, 10);
        $bindings['GameAuditsCore'] = $gameAudits;

        // Import rules
        $bindings['ImportRulesEshop'] = $this->repoGameImportRuleEshop->getByGameId($gameId);

        // Flags
        $bindings['GameFlags'] = $this->repoGameFlag->getByGameId($gameId);

        // Other console versions (#44). Bound here rather than read as GameData.gameGroup:
        // for an ungrouped game Twig falls back to the gameGroup() relation object, which is truthy.
        $gameGroup = $game->gameGroup;
        $bindings['GameGroup'] = $gameGroup;
        $bindings['GameGroupOthers'] = $gameGroup ? $gameGroup->games->where('id', '!=', $game->id) : collect();

        // Checks
        $checks = [
            [
                'label' => 'Category',
                'status' => $game->category ? 'ok' : 'fail',
                //'note' => $game->category ? '' : 'None',
                'href' => route('staff.games.edit', ['gameId' => $game->id])
            ],
            [
                'label' => 'Publishers',
                'status' => $game->gamePublishers->count() > 0 ? 'ok' : 'fail',
                //'note' => $game->gamePublishers ? '' : 'None',
                'href' => route('staff.games.partner.list', ['gameId' => $game->id])
            ],
            [
                'label' => 'Release date',
                'status' => $game->eu_release_date ? 'ok' : 'fail',
                //'note' => $game->category ? '' : 'None',
                'href' => route('staff.games.edit', ['gameId' => $game->id])
            ],
            [
                'label' => 'Players',
                'status' => $game->players ? 'ok' : 'fail',
                //'note' => $game->category ? '' : 'None',
                'href' => route('staff.games.edit', ['gameId' => $game->id])
            ],
            [
                'label' => 'Price',
                'status' => $game->price_eshop ? 'ok' : 'fail',
                //'note' => $game->category ? '' : 'None',
                'href' => route('staff.games.edit', ['gameId' => $game->id])
            ],
            [
                'label' => 'Category verif.',
                'status' => $game->category_verification == 1 ? 'ok' : 'fail',
                //'note' => $game->category ? '' : 'None',
                'href' => route('staff.games.edit', ['gameId' => $game->id])
            ],
        ];

        $bindings['Checks'] = $checks;

        // Crawl lifecycle history
        $bindings['CrawlLifecycle'] = $game->crawlLifecycle()->orderBy('crawled_at', 'desc')->limit(20)->get();

        return view('staff.games.detail.show', $bindings);
    }

    public function showFullAudit(Game $game)
    {
        $gameId = $game->id;

        $pageTitle = 'Full audit history: '.$game->title;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesDetailSubpage($pageTitle, $game))->bindings;

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

        $dsItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);
        if (!$dsItem) {
            return response()->json(['error' => 'Cannot find NintendoCoUk source data for this game'], 400);
        }

        $gameImportRule = $this->repoGameImportRuleEshop->getByGameId($gameId);

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

        $dsItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);
        if (!$dsItem) {
            return response()->json(['error' => 'Cannot find NintendoCoUk source data for this game'], 400);
        }

        $this->downloadPackshotHelper->downloadForGame($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function updateStatus($gameId)
    {
        $request = request();

        $newStatus = $request->input('status');
        if (!$newStatus) {
            return response()->json(['error' => 'Missing data: status'], 400);
        }

        $validStatuses = array_keys(GameStatus::options());
        if (!in_array($newStatus, $validStatuses)) {
            return response()->json(['error' => 'Invalid status value'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        $game->game_status = $newStatus;
        $game->save();

        // Clear cache
        $this->repoGame->clearCacheCoreData($gameId);

        return response()->json(['status' => 'OK', 'new_status' => $newStatus], 200);
    }

    public function queueCrawl($gameId)
    {
        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Cannot find game!'], 400);
        }

        $game->crawl_priority = true;
        $game->save();

        // Clear cache
        $this->repoGame->clearCacheCoreData($gameId);

        return response()->json(['status' => 'OK'], 200);
    }

}