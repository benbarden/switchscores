<?php

namespace App\Http\Controllers\Staff\DataSources;

use Illuminate\Routing\Controller as Controller;

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
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;

class DataSourceParsedController extends Controller
{
    public function __construct(
        private GamesCompanyRepository $repoGamesCompany,
        private GamePublisherRepository $repoGamePublisher,
        private LinkTitle $urlLinkTitle,
        private DataSourceRepository $repoDataSource,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameTitleHashRepository $repoGameTitleHash,
        private HashGeneratorRepository $gameTitleHashGenerator,
        private DownloadPackshotHelper $downloadPackshotHelper
    ){
    }

    public function nintendoCoUkUnlinkedItems()
    {
        $dataSource = $this->repoDataSource->getSourceNintendoCoUk();
        if (!$dataSource) abort(404);

        $pageTitle = $dataSource->name.' - Unlinked items';
        $tableSort = "[ 2, 'asc' ]";

        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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

        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->dataSourcesNintendoCoUkUnlinkedSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['DSParsedItem'] = $dsParsedItem;
        $customErrors = [];

        $bindings['ItemId'] = $itemId;

        $request = request();
        $okToProceed = true;

        if ($request->isMethod('post')) {

            $title = $dsParsedItem->title;

            // Perform common title replacements
            $title = str_replace('®', '', $title);
            $title = str_replace('™', '', $title);
            $title = str_replace(' – ', ': ', $title);

            // Check title hash is unique
            $titleLowercase = strtolower($title);
            $hashedTitle = $this->gameTitleHashGenerator->generateHash($title);
            $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);

            // Switch 2 duplicate title check
            if ($dsParsedItem->console->id == Console::ID_SWITCH_2 && $hashExists) {
                // Generate new title hash
                $title .= ' (Switch 2)';
                $titleLowercase = strtolower($title);
                $hashedTitle = $this->gameTitleHashGenerator->generateHash($title);
                $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);
            }

            // Check for duplicates
            if ($hashExists) {
                $customErrors[] = 'Title already exists for another record!';
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
                $gameTitleHash = $this->repoGameTitleHash->create($titleLowercase, $hashedTitle, $gameId);

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
