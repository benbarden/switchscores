<?php

namespace App\Http\Controllers\Staff\DataSources;

use App\Construction\GameImportRule\EshopBuilder;
use App\Construction\GameImportRule\EshopDirector;
use App\Models\DataSource;
use App\Services\DataSources\Queries\Differences;
use App\Traits\StaffView;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class DifferencesController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function getCategoryId($genresJson)
    {
        $genresArray = json_decode($genresJson);
        $genreCount = count($genresArray);
        $categoryName = null;
        $category = null;

        if ($genreCount == 0) {
            throw new \Exception('No genres to apply!');
        }

        // Make it consistent
        foreach ($genresArray as &$genre) {
            $genre = ucfirst($genre);
        }
        sort($genresArray);

        // Handle acceptable matches
        if ($genreCount == 1) {
            if ($genresArray[0] == 'Point and click adventure') {
                $categoryName = 'Adventure';
            } elseif ($genresArray[0] == 'Point-and-click adventure') {
                $categoryName = 'Adventure';
            } else {
                $categoryName = $genresArray[0];
            }
        } else {
            if (array_diff($genresArray, ['Adventure', 'Role-playing']) == null) {
                $categoryName = 'Adventure RPG';
            } elseif (array_diff($genresArray, ['Action', 'Adventure']) == null) {
                $categoryName = 'Action-adventure';
            } elseif (array_diff($genresArray, ['Adventure', 'Puzzle']) == null) {
                $categoryName = 'Puzzle adventure';
            }
        }

        $category = $this->getServiceCategory()->getByName($categoryName);
        if (!$category) {
            throw new \Exception('Cannot auto-match this genre combination to a category.');
        }

        $categoryId = $category->id;
        return $categoryId;
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
                } elseif ($sourceField == 'dsp_genres') {
                    try {
                        $categoryId = $this->getCategoryId($dsParsedItem->genres_json);
                    } catch (\Exception $e) {
                        return response()->json(['error' => $e->getMessage()], 400);
                    }
                    $game->category_id = $categoryId;
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

                $serviceImportRuleEshop = $this->getServiceGameImportRuleEshop();
                $gameImportRuleEshop = $serviceImportRuleEshop->getByGameId($gameId);
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
                } elseif ($sourceField == 'dsp_genres') {
                    $importRuleParams['ignore_genres'] = 'on';
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
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getReleaseDateEUNintendoCoUk();

        $bindings['GameField'] = 'eu_release_date';
        $bindings['SourceField'] = 'release_date_eu';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPrice()
    {
        $pageTitle = 'Differences: Price - Nintendo.co.uk API';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPriceNintendoCoUk();

        $bindings['GameField'] = 'price_eshop';
        $bindings['SourceField'] = 'price_standard';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPlayers()
    {
        $pageTitle = 'Differences: Players - Nintendo.co.uk API';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPlayersNintendoCoUk();

        $bindings['GameField'] = 'game_players';
        $bindings['SourceField'] = 'dsp_players';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkPublishers()
    {
        $pageTitle = 'Differences: Publishers - Nintendo.co.uk API';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getPublishersNintendoCoUk();

        $bindings['GameField'] = 'game_publishers';
        $bindings['SourceField'] = 'dsp_publishers';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;
        $bindings['HideApplyChange'] = 'Y';

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }

    public function nintendoCoUkGenres()
    {
        $pageTitle = 'Differences: Genres - Nintendo.co.uk API';
        $bindings = $this->getBindingsDataSourcesSubpage($pageTitle);

        $dsDifferences = new Differences();
        $bindings['DifferenceList'] = $dsDifferences->getGenresNintendoCoUk();

        $bindings['GameField'] = 'category_name';
        $bindings['SourceField'] = 'dsp_genres';
        $bindings['DataSourceId'] = $this->getServiceDataSource()->getSourceNintendoCoUk()->id;

        $highlightGameId = \Request::get('gameid');
        $bindings['HighlightGameId'] = $highlightGameId;

        return view('staff.data-sources.differences.view-differences', $bindings);
    }
}
