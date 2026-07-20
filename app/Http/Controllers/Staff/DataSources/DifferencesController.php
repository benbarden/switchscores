<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\GameImportRuleEshop\Repository as GameImportRuleEshopRepository;

use App\Construction\GameImportRule\EshopBuilder;
use App\Construction\GameImportRule\EshopDirector;
use App\Models\DataSource;
use App\Services\DataSources\Queries\Differences;

class DifferencesController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame,
        private DataSourceRepository $repoDataSource,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameImportRuleEshopRepository $repoGameImportRuleEshop
    )
    {

    }

    public function applyChange()
    {
        $request = request();

        $gameId = $request->gameId;
        $dataSourceId = $request->dataSourceId;
        $sourceField = $request->sourceField;

        if (!$gameId || !$dataSourceId || !$sourceField) {
            return response()->json(['error' => 'Missing required parameter(s)'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        switch ($dataSourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $dsParsedItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);
                if (!$dsParsedItem) {
                    return response()->json(['error' => 'DS parsed item not found for game: '.$gameId], 400);
                }

                if ($sourceField == 'release_date_eu') {
                    $game->eu_release_date = $dsParsedItem->release_date_eu;
                } elseif ($sourceField == 'price_standard') {
                    $game->price_eshop = $dsParsedItem->price_standard;
                } elseif ($sourceField == 'dsp_players') {
                    $game->players = $dsParsedItem->players;
                } else {
                    return response()->json(['error' => 'NOT SUPPORTED'], 400);
                }

                $game->save();

                return response()->json(['status' => 'OK'], 200);

                break;

            default:
                return response()->json(['error' => 'NOT SUPPORTED'], 400);
                break;

        }

    }

    public function ignoreChange()
    {
        $request = request();

        $gameId = $request->gameId;
        $dataSourceId = $request->dataSourceId;
        $sourceField = $request->sourceField;

        if (!$gameId || !$dataSourceId || !$sourceField) {
            return response()->json(['error' => 'Missing required parameter(s)'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        switch ($dataSourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $dsParsedItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);
                if (!$dsParsedItem) {
                    return response()->json(['error' => 'DS parsed item not found for game: '.$gameId], 400);
                }

                $gameImportRuleEshop = $this->repoGameImportRuleEshop->getByGameId($gameId);
                if ($gameImportRuleEshop) {
                    $importRuleParams = [
                        'ignore_europe_dates' => $gameImportRuleEshop->ignore_europe_date,
                        'ignore_price' => $gameImportRuleEshop->ignore_price,
                        'ignore_players' => $gameImportRuleEshop->ignore_players,
                        'ignore_publishers' => $gameImportRuleEshop->ignore_publishers,
                        'ignore_genres' => $gameImportRuleEshop->ignore_genres,
                    ];
                } else {
                    $importRuleParams = [];
                }

                if ($sourceField == 'release_date_eu') {
                    $importRuleParams['ignore_europe_dates'] = 'on';
                } elseif ($sourceField == 'price_standard') {
                    $importRuleParams['ignore_price'] = 'on';
                } elseif ($sourceField == 'dsp_players') {
                    $importRuleParams['ignore_players'] = 'on';
                } elseif ($sourceField == 'dsp_publishers') {
                    $importRuleParams['ignore_publishers'] = 'on';
                } else {
                    return response()->json(['error' => 'NOT SUPPORTED'], 400);
                }

                // Update the DB
                $importRuleDirector = new EshopDirector();
                $importRuleBuilder = new EshopBuilder();
                $importRuleDirector->setBuilder($importRuleBuilder);
                if ($gameImportRuleEshop) {
                    $importRuleDirector->buildExisting($gameImportRuleEshop, $importRuleParams);
                } else {
                    $importRuleBuilder->setGameId($gameId);
                    $importRuleDirector->buildNew($importRuleParams);
                }
                $importRule = $importRuleBuilder->getGameImportRule();
                $importRule->save();

                return response()->json(['status' => 'OK'], 200);

                break;

            default:
                return response()->json(['error' => 'NOT SUPPORTED'], 400);
                break;

        }

    }

    public function nintendoCoUkEuReleaseDate()
    {
        $pageTitle = 'Differences: EU release date - Nintendo.co.uk API';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getReleaseDateEUNintendoCoUk();

        $bindings['GameField'] = 'eu_release_date';
        $bindings['SourceField'] = 'release_date_eu';
        $bindings['DataSourceId'] = $this->repoDataSource->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPrice()
    {
        $pageTitle = 'Differences: Price - Nintendo.co.uk API';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPriceNintendoCoUk();

        $bindings['GameField'] = 'price_eshop';
        $bindings['SourceField'] = 'price_standard';
        $bindings['DataSourceId'] = $this->repoDataSource->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPlayers()
    {
        $pageTitle = 'Differences: Players - Nintendo.co.uk API';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPlayersNintendoCoUk();

        $bindings['GameField'] = 'game_players';
        $bindings['SourceField'] = 'dsp_players';
        $bindings['DataSourceId'] = $this->repoDataSource->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPublishers()
    {
        $pageTitle = 'Differences: Publishers - Nintendo.co.uk API';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle))->bindings;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPublishersNintendoCoUk();

        $bindings['GameField'] = 'game_publishers';
        $bindings['SourceField'] = 'dsp_publishers';
        $bindings['DataSourceId'] = $this->repoDataSource->getSourceNintendoCoUk()->id;
        $bindings['HideApplyChange'] = 'Y';

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

}
