<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Models\DataSource;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Models\Console;
use App\Models\Game;
use App\Events\GameCreated;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Factories\GameDirectorFactory;
use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Domain\Url\LinkTitle;

use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\DataSource\Repository as DataSourceRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\DataSourceRaw\Repository as DataSourceRawRepository;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;

class DataSourceParsedController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GamesCompanyRepository $repoGamesCompany,
        private GamePublisherRepository $repoGamePublisher,
        private LinkTitle $urlLinkTitle,
        private DataSourceRepository $repoDataSource,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private DataSourceRawRepository $repoDataSourceRaw,
        private GameTitleHashRepository $repoGameTitleHash,
        private HashGeneratorRepository $gameTitleHashGenerator,
        private DownloadPackshotHelper $downloadPackshotHelper
    ){
    }

    public function showList($sourceId)
    {
        $dataSource = $this->repoDataSource->find($sourceId);
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Parsed items';
        $tableSort = "[ 1, 'asc' ]";
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle), jsInitialSort: $tableSort)->bindings;

        $request = request();
        $searchTitle = $request->searchTitle;
        $filterLinked = $request->filterLinked;
        $filterEuDate = $request->filterEuDate;

        $hasFilters = $searchTitle || $filterLinked || $filterEuDate;

        $ignoreIdList = $this->repoDataSourceIgnore->getAllBySource($sourceId)->pluck('link_id')->toArray();

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;
        $bindings['SearchTitle'] = $searchTitle ?? '';
        $bindings['FilterLinked'] = $filterLinked ?? '';
        $bindings['FilterEuDate'] = $filterEuDate ?? '';
        $bindings['IgnoreIdList'] = $ignoreIdList;
        $bindings['ItemList'] = $hasFilters
            ? $this->repoDataSourceParsed->getBySourceFiltered($sourceId, $searchTitle, $filterLinked, $filterEuDate)
            : null;

        return view('staff.data-sources.parsed.list', $bindings);
    }

    public function viewParsed($sourceId, $linkId)
    {
        $dataSource = $this->repoDataSource->find($sourceId);
        if (!$dataSource) abort(404);

        $dsParsedItem = $this->repoDataSourceParsed->getBySourceAndLinkId($sourceId, $linkId);
        if (!$dsParsedItem) abort(404);

        $pageTitle = $dsParsedItem->title;
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesListParsedSubpage($pageTitle, $dataSource))->bindings;

        $ignoreIdList = $this->repoDataSourceIgnore->getAllBySource($sourceId)->pluck('link_id')->toArray();

        $rawItem = $this->repoDataSourceRaw->findBySourceIdAndLinkId($sourceId, $linkId);

        $bindings['DSParsedItem'] = $dsParsedItem;
        $bindings['IgnoreIdList'] = $ignoreIdList;
        $bindings['RawItem'] = $rawItem;
        $bindings['SourceDataRaw'] = $rawItem ? json_decode($rawItem->source_data_json, true) : null;

        return view('staff.data-sources.parsed.view', $bindings);
    }

    public function nintendoCoUkUnlinkedItems()
    {
        $dataSource = $this->repoDataSource->getSourceNintendoCoUk();
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Unlinked items';
        $tableSort = "[ 2, 'asc' ]";

        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle), jsInitialSort: $tableSort)->bindings;

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();

        $bindings['ItemsWithEUDate'] = $this->repoDataSourceParsed->getNintendoCoUkUnlinkedWithEUDate($ignoreIdList);
        $bindings['ItemsNoEUDate'] = $this->repoDataSourceParsed->getNintendoCoUkUnlinkedNoEUDate($ignoreIdList);
        $bindings['ListRef'] = 'unlinked';

        return view('staff.data-sources.parsed.list-unlinked', $bindings);
    }

    public function nintendoCoUkIgnoredItems()
    {
        $dataSource = $this->repoDataSource->getSourceNintendoCoUk();
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Ignored items';
        $tableSort = "[ 2, 'asc' ]";

        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesSubpage($pageTitle), jsInitialSort: $tableSort)->bindings;

        $bindings['SourceId'] = $dataSource->id;
        $bindings['DataSource'] = $dataSource;

        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();

        $bindings['ItemList'] = $this->repoDataSourceParsed->getAllNintendoCoUkInLinkIdList($ignoreIdList);
        $bindings['ListRef'] = 'ignored';

        return view('staff.data-sources.parsed.list-ignored', $bindings);
    }

    public function addGameNintendoCoUk($itemId)
    {
        $dsParsedItem = $this->repoDataSourceParsed->find($itemId);
        if (!$dsParsedItem) abort(404);

        $pageTitle = 'Add game from Nintendo.co.uk API';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::dataSourcesNintendoUnlinkedSubpage($pageTitle))->bindings;

        $bindings['DSParsedItem'] = $dsParsedItem;
        $customErrors = [];

        $bindings['ItemId'] = $itemId;

        $request = request();
        $okToProceed = true;

        if ($request->isMethod('post')) {

            $title = $dsParsedItem->title;
            $consoleId = $dsParsedItem->console_id;

            // Perform common title replacements
            $title = str_replace('®', '', $title);
            $title = str_replace('™', '', $title);
            $title = str_replace(' – ', ': ', $title);

            // Check title hash is unique for this console
            $titleLowercase = strtolower($title);
            $hashedTitle = $this->gameTitleHashGenerator->generateHash($title);
            $hashExists = $this->repoGameTitleHash->titleHashExistsForConsole($hashedTitle, $consoleId);

            // Check for duplicates on this console
            if ($hashExists) {
                $customErrors[] = 'Title already exists for another game on this console!';
                $okToProceed = false;
            }

            if ($okToProceed) {

                // Generate usable game data
                $linkText = $this->urlLinkTitle->generate($title);

                $gameData = [
                    'title' => $title,
                    'console_id' => $dsParsedItem->console_id,
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
                $gameTitleHash = $this->repoGameTitleHash->create($titleLowercase, $hashedTitle, $gameId, $consoleId);

                // Update eShop data
                $game = $game->fresh();
                UpdateGameFactory::doUpdate($game, $dsParsedItem);

                // Add game id to parsed item.
                // Must do this before downloading packshots or they won't be found.
                $dsParsedItem->game_id = $gameId;
                $dsParsedItem->save();

                // Download packshots
                $this->downloadPackshotHelper->downloadForGame($game);

                // Set digital format
                $game->format_digital = Game::FORMAT_AVAILABLE;
                $game->save();

                // Add publishers, if they exist
                $customGameDetailMsg = '';
                $gamesCompany = $this->repoGamesCompany->getByName($dsParsedItem->publishers);
                if ($gamesCompany) {
                    $this->repoGamePublisher->create($gameId, $gamesCompany->id);
                } else {
                    $customGameDetailMsg = '&alertmsg=publishernotadded&dsitemid='.$dsParsedItem->id;
                }

                // Set game to low quality if publisher is low quality
                if ($gamesCompany) {
                    if ($gamesCompany->is_low_quality == 1) {
                        $game->is_low_quality = 1;
                        $game->save();
                    }
                }

                // Trigger event
                event(new GameCreated($game));

                $redirectUrl = '/staff/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId.$customGameDetailMsg;

                return redirect($redirectUrl);

            }

        }

        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.data-sources.parsed.add-game', $bindings);
    }

}
