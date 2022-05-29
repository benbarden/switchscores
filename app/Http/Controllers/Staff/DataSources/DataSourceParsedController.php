<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Models\Game;
use App\Events\GameCreated;
use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Factories\DataSource\Wikipedia\UpdateGameFactory as WikiUpdateGameFactory;
use App\Factories\GameDirectorFactory;
use App\Services\UrlService;

use App\Traits\SwitchServices;

class DataSourceParsedController extends Controller
{
    use SwitchServices;

    public function nintendoCoUkUnlinkedItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceNintendoCoUk();
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Unlinked items';
        $tableSort = "[ 2, 'asc' ]";

        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();

        $bindings['ItemsWithEUDate'] = $this->getServiceDataSourceParsed()->getNintendoCoUkUnlinkedWithEUDate($ignoreIdList);
        $bindings['ItemsNoEUDate'] = $this->getServiceDataSourceParsed()->getNintendoCoUkUnlinkedNoEUDate($ignoreIdList);
        $bindings['ListRef'] = 'unlinked';

        return view('staff.data-sources.parsed.list-unlinked', $bindings);
    }

    public function nintendoCoUkIgnoredItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceNintendoCoUk();
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Ignored items';
        $tableSort = "[ 2, 'asc' ]";

        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();

        $bindings['ItemList'] = $this->getServiceDataSourceParsed()->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['ListRef'] = 'ignored';

        return view('staff.data-sources.parsed.list-ignored', $bindings);
    }

    public function addGameNintendoCoUk($itemId)
    {
        $dsParsedItem = $this->getServiceDataSourceParsed()->find($itemId);
        if (!$dsParsedItem) abort(404);

        $pageTitle = 'Add game from Nintendo.co.uk API';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesNintendoCoUkUnlinkedSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['DSParsedItem'] = $dsParsedItem;
        $customErrors = [];

        $serviceGameTitleHash = $this->getServiceGameTitleHash();
        $serviceUrl = new UrlService();

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

                // Set digital format
                $game->format_digital = Game::FORMAT_AVAILABLE;
                $game->save();

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
        $tableSort = "[ 1, 'asc' ]";

        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreTitleList = $this->getServiceDataSourceIgnore()->getWikipediaTitleList();

        $bindings['ItemsWithEUDate'] = $this->getServiceDataSourceParsed()->getWikipediaNoGameIdWithEUDate($ignoreTitleList);
        $bindings['ItemsNoEUDate'] = $this->getServiceDataSourceParsed()->getWikipediaNoGameIdNoEUDate($ignoreTitleList);
        $bindings['ListRef'] = 'unlinked';

        return view('staff.data-sources.parsed.list-unlinked', $bindings);
    }

    public function wikipediaIgnoredItems()
    {
        $dataSource = $this->getServiceDataSource()->getSourceWikipedia();
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Ignored items';
        $tableSort = "[ 1, 'asc' ]";

        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreTitleList = $this->getServiceDataSourceIgnore()->getWikipediaTitleList();

        $bindings['ItemList'] = $this->getServiceDataSourceParsed()->getAllWikipediaInTitleList($ignoreTitleList);
        $bindings['ListRef'] = 'ignored';

        return view('staff.data-sources.parsed.list-ignored', $bindings);
    }

    public function wikipediaAddLink($itemId)
    {
        $dsParsedItem = $this->getServiceDataSourceParsed()->find($itemId);
        if (!$dsParsedItem) abort(404);

        if (!$dsParsedItem->isSourceWikipedia()) abort(500);

        if ($dsParsedItem->game_id != null) redirect(route('staff.data-sources.dashboard'));

        $pageTitle = 'Add game from Wikipedia';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesWikipediaUnlinkedSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $customErrors = [];

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
