<?php

namespace App\Http\Controllers\Staff\DataSources;

use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Factories\DataSource\Wikipedia\UpdateGameFactory as WikiUpdateGameFactory;
use Illuminate\Routing\Controller as Controller;

use App\Services\UrlService;
use App\Factories\GameDirectorFactory;
use App\Events\GameCreated;

use App\Traits\SwitchServices;

class DataSourceParsedController extends Controller
{
    use SwitchServices;

    public function nintendoCoUkUnlinkedItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceNintendoCoUk();

        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Unlinked items';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();

        $bindings['ItemList'] = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        $bindings['jsInitialSort'] = "[ 2, 'asc' ]";
        $bindings['ListRef'] = 'unlinked';

        return view('staff.data-sources.parsed.list', $bindings);
    }

    public function nintendoCoUkIgnoredItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceNintendoCoUk();

        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Ignored items';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();

        $bindings['ItemList'] = $this->getServiceDataSourceParsed()->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['jsInitialSort'] = "[ 2, 'asc' ]";
        $bindings['ListRef'] = 'ignored';

        return view('staff.data-sources.parsed.list', $bindings);
    }

    public function addGameNintendoCoUk($itemId)
    {
        $bindings = [];
        $customErrors = [];

        $dsParsedItem = $this->getServiceDataSourceParsed()->find($itemId);
        if (!$dsParsedItem) abort(404);

        $bindings['DSParsedItem'] = $dsParsedItem;

        $serviceGameTitleHash = $this->getServiceGameTitleHash();
        $serviceUrl = new UrlService();

        $bindings['TopTitle'] = 'Add game from Nintendo.co.uk API';
        $bindings['PageTitle'] = 'Add game from Nintendo.co.uk API';

        $bindings['ItemId'] = $itemId;

        $request = request();
        $okToProceed = true;

        if ($request->isMethod('post')) {

            $title = $dsParsedItem->title;

            // Check title hash is unique
            $titleLowercase = strtolower($title);
            $hashedTitle = $serviceGameTitleHash->generateHash($title);
            $existingTitleHash = $serviceGameTitleHash->getByHash($hashedTitle);

            // Check for duplicates
            if ($existingTitleHash != null) {
                $customErrors[] = 'Title already exists for another record! Game id: '.$existingTitleHash->game_id;
                $okToProceed = false;
            }

            if ($okToProceed) {

                // Generate usable game data
                $linkText = $serviceUrl->generateLinkText($title);

                $gameData = [
                    'title' => $title,
                    'link_title' => $linkText,
                    'eshop_europe_fs_id' => $dsParsedItem->link_id,
                    //'eu_release_date' => $dsParsedItem->release_date_eu,
                    'price_eshop' => $dsParsedItem->price_standard,
                    'players' => $dsParsedItem->players,
                    //'publisher' => $dsParsedItem->publishers,
                ];

                // Save details
                $game = GameDirectorFactory::createNew($gameData);
                $gameId = $game->id;

                // Add title hash
                $gameTitleHash = $serviceGameTitleHash->create($titleLowercase, $hashedTitle, $gameId);

                // Update eShop data
                $game = $game->fresh();
                UpdateGameFactory::doUpdate($game, $dsParsedItem);
                DownloadImageFactory::downloadImages($game, $dsParsedItem);

                // Add game id to parsed item
                $dsParsedItem->game_id = $gameId;
                $dsParsedItem->save();

                // Trigger event
                event(new GameCreated($game));

                return redirect('/staff/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId);

            }

        }

        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.data-sources.parsed.add-game', $bindings);
    }

    public function wikipediaUnlinkedItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceWikipedia();

        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Unlinked items';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreTitleList = $this->getServiceDataSourceIgnore()->getWikipediaTitleList();

        $bindings['ItemList'] = $this->getServiceDataSourceParsed()->getAllWikipediaWithNoGameId($ignoreTitleList);
        $bindings['jsInitialSort'] = "[ 1, 'asc' ]";
        $bindings['ListRef'] = 'unlinked';

        return view('staff.data-sources.parsed.list', $bindings);
    }

    public function wikipediaIgnoredItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceWikipedia();

        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Ignored items';

        $bindings = [];

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreTitleList = $this->getServiceDataSourceIgnore()->getWikipediaTitleList();

        $bindings['ItemList'] = $this->getServiceDataSourceParsed()->getAllWikipediaInTitleList($ignoreTitleList);
        $bindings['jsInitialSort'] = "[ 1, 'asc' ]";
        $bindings['ListRef'] = 'ignored';

        return view('staff.data-sources.parsed.list', $bindings);
    }

    public function wikipediaAddLink($itemId)
    {
        $bindings = [];
        $customErrors = [];

        $dsParsedItem = $this->getServiceDataSourceParsed()->find($itemId);

        if (!$dsParsedItem) abort (404);

        if (!$dsParsedItem->isSourceWikipedia()) abort(500);

        if ($dsParsedItem->game_id != null) redirect(route('staff.data-sources.dashboard'));

        $request = request();
        $okToProceed = true;

        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        if ($request->isMethod('post')) {

            $title = $dsParsedItem->title;

            // Check title hash is unique
            $titleLowercase = strtolower($title);
            $hashedTitle = $serviceGameTitleHash->generateHash($title);
            $existingTitleHash = $serviceGameTitleHash->getByHash($hashedTitle);

            // Check for duplicates
            if ($existingTitleHash != null) {
                $customErrors[] = 'Title already exists for another record! Game id: '.$existingTitleHash->game_id;
                $okToProceed = false;
            }

            // Check game exists
            $gameId = $request->game_id;
            $game = $this->getServiceGame()->find($gameId);
            if (!$game) {
                $customErrors[] = 'Game not found! '.$gameId;
                $okToProceed = false;
            }

            if ($okToProceed) {

                // Add title hash
                $gameTitleHash = $serviceGameTitleHash->create($titleLowercase, $hashedTitle, $gameId);

                // Add game id to parsed item
                $dsParsedItem->game_id = $gameId;
                $dsParsedItem->save();

                // Update game
                $gameImportRule = $this->getServiceGameImportRuleWikipedia()->getByGameId($gameId);
                WikiUpdateGameFactory::doUpdate($game, $dsParsedItem, $gameImportRule);

                return redirect(route('staff.data-sources.wikipedia.unlinked'));

            }

        }

        $pageTitle = 'Wikipedia - Add link';

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        //$bindings['SourceId'] = $dataSource->id;
        //$bindings['DataSource'] = $dataSource;

        $bindings['DSParsedItem'] = $dsParsedItem;
        $bindings['ItemId'] = $itemId;

        $bindings['GameList'] = $this->getServiceGame()->getAll();

        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.data-sources.parsed.wikipedia.add-link', $bindings);
    }

}
