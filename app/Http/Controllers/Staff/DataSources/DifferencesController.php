<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\DataSource;
use App\Services\DataSources\Queries\Differences;
use App\Construction\GameImportRule\EshopBuilder;
use App\Construction\GameImportRule\EshopDirector;
use App\Construction\GameImportRule\WikipediaBuilder;
use App\Construction\GameImportRule\WikipediaDirector;

use App\Traits\SwitchServices;

class DifferencesController extends Controller
{
    use SwitchServices;

    public function applyChange()
    {
        $request = request();

        $gameId = $request->gameId;
        $dataSourceId = $request->dataSourceId;
        $sourceField = $request->sourceField;

        if (!$gameId || !$dataSourceId || !$sourceField) {
            return response()->json(['error' => 'Missing required parameter(s)'], 400);
        }

        $game = $this->getServiceGame()->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        switch ($dataSourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $dsParsedItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
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

            case DataSource::DSID_WIKIPEDIA:

                $dsParsedItem = $this->getServiceDataSourceParsed()->getSourceWikipediaForGame($gameId);
                if (!$dsParsedItem) {
                    return response()->json(['error' => 'DS parsed item not found for game: '.$gameId], 400);
                }

                if ($sourceField == 'release_date_eu') {
                    $game->eu_release_date = $dsParsedItem->release_date_eu;
                } elseif ($sourceField == 'release_date_us') {
                    $game->us_release_date = $dsParsedItem->release_date_us;
                } elseif ($sourceField == 'release_date_jp') {
                    $game->jp_release_date = $dsParsedItem->release_date_jp;
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

        $game = $this->getServiceGame()->find($gameId);
        if (!$game) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        switch ($dataSourceId) {

            case DataSource::DSID_NINTENDO_CO_UK:

                $dsParsedItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
                if (!$dsParsedItem) {
                    return response()->json(['error' => 'DS parsed item not found for game: '.$gameId], 400);
                }

                if ($sourceField == 'release_date_eu') {
                    $importRuleParams = ['ignore_europe_dates' => 'on'];
                } elseif ($sourceField == 'price_standard') {
                    $importRuleParams = ['ignore_price' => 'on'];
                } elseif ($sourceField == 'dsp_players') {
                    $importRuleParams = ['ignore_players' => 'on'];
                } else {
                    return response()->json(['error' => 'NOT SUPPORTED'], 400);
                }

                $serviceImportRuleEshop = $this->getServiceGameImportRuleEshop();
                $gameImportRuleEshop = $serviceImportRuleEshop->getByGameId($gameId);
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

            case DataSource::DSID_WIKIPEDIA:

                $dsParsedItem = $this->getServiceDataSourceParsed()->getSourceWikipediaForGame($gameId);
                if (!$dsParsedItem) {
                    return response()->json(['error' => 'DS parsed item not found for game: '.$gameId], 400);
                }

                if ($sourceField == 'release_date_eu') {
                    $importRuleParams = ['ignore_europe_dates' => 'on'];
                } elseif ($sourceField == 'release_date_us') {
                    $importRuleParams = ['ignore_us_dates' => 'on'];
                } elseif ($sourceField == 'release_date_jp') {
                    $importRuleParams = ['ignore_jp_dates' => 'on'];
                } else {
                    return response()->json(['error' => 'NOT SUPPORTED'], 400);
                }

                $serviceImportRuleWikipedia = $this->getServiceGameImportRuleWikipedia();
                $gameImportRuleWikipedia = $serviceImportRuleWikipedia->getByGameId($gameId);
                // Update the DB
                $importRuleDirector = new WikipediaDirector();
                $importRuleBuilder = new WikipediaBuilder();
                $importRuleDirector->setBuilder($importRuleBuilder);
                if ($gameImportRuleWikipedia) {
                    $importRuleDirector->buildExisting($gameImportRuleWikipedia, $importRuleParams);
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

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getReleaseDateEUNintendoCoUk();

        $bindings['GameField'] = 'eu_release_date';
        $bindings['SourceField'] = 'release_date_eu';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPrice()
    {
        $pageTitle = 'Differences: Price - Nintendo.co.uk API';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPriceNintendoCoUk();

        $bindings['GameField'] = 'price_eshop';
        $bindings['SourceField'] = 'price_standard';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPlayers()
    {
        $pageTitle = 'Differences: Players - Nintendo.co.uk API';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPlayersNintendoCoUk();

        $bindings['GameField'] = 'game_players';
        $bindings['SourceField'] = 'dsp_players';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function wikipediaEuReleaseDate()
    {
        $pageTitle = 'Differences: EU release date - Wikipedia';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getReleaseDateEUWikipedia();

        $bindings['GameField'] = 'eu_release_date';
        $bindings['SourceField'] = 'release_date_eu';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceWikipedia()->id;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function wikipediaUsReleaseDate()
    {
        $pageTitle = 'Differences: US release date - Wikipedia';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getReleaseDateUSWikipedia();

        $bindings['GameField'] = 'us_release_date';
        $bindings['SourceField'] = 'release_date_us';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceWikipedia()->id;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function wikipediaJpReleaseDate()
    {
        $pageTitle = 'Differences: JP release date - Wikipedia';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getReleaseDateJPWikipedia();

        $bindings['GameField'] = 'jp_release_date';
        $bindings['SourceField'] = 'release_date_jp';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceWikipedia()->id;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }
}
