<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Category\Repository as CategoryRepository;
use App\Models\Console;
use App\Construction\Game\GameBuilder;

use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\Url\LinkTitle as LinkTitleGenerator;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\MissingCategory as GameListMissingCategoryRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;

use Carbon\Carbon;

class BulkEditorController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory,
        private GameTitleHashRepository $repoGameTitleHash,
        private HashGeneratorRepository $gameTitleHashGenerator,
        private GameQualityFilter $gameQualityFilter,
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists,
        private GameListMissingCategoryRepository $repoGameListMissingCategory,
        private GamesCompanyRepository $repoGamesCompany,
        private LinkTitleGenerator $linkTitleGenerator,
        private GamePublisherRepository $repoGamePublisher
    )
    {
    }

    public function eshopUpcomingCrosscheck($consoleId)
    {
        $pageTitle = 'Bulk edit games';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $gameList = $this->repoGameLists->upcomingEshopCrosscheck($consoleId);
        $bindings['GameList'] = $gameList;
        $bindings['ConsoleId'] = $consoleId;

        return view('staff.games.bulk-edit.eshop-upcoming-crosscheck', $bindings);

    }

    private function getEditModeBindings($editMode, $bindings)
    {
        $editModeHeader1 = '';
        $editModeHeader2 = '';
        $templateEditCell = '';
        $templateScripts = '';

        switch ($editMode) {
            case 'category':
            case 'category_sim':
            case 'category_survival':
            case 'category_puzzle':
            case 'category_sports_racing':
            case 'category_quiz':
            case 'category_spot_the_difference':
            case 'category_hidden':
            case 'category_escape':
            case 'category_hentai_girls':
            case 'category_drone_flying_tour':
                $editModeHeader1 = 'Category';
                $templateEditCell = 'category/edit-cell.twig';
                $templateScripts = 'category/scripts.twig';
                break;
            case 'eshop_europe_order':
            case 'eshop_upcoming_crosscheck':
                $editModeHeader1 = 'eShop Europe order';
                $templateEditCell = 'eshop-europe-order/edit-cell.twig';
                $templateScripts = 'eshop-europe-order/scripts.twig';
                break;
        }
        $bindings['EditModeHeader1'] = $editModeHeader1;
        $bindings['EditModeHeader2'] = $editModeHeader2;
        $bindings['TemplateEditCell'] = $templateEditCell;
        $bindings['TemplateScripts'] = $templateScripts;

        return $bindings;
    }

    private function getFieldToUpdate($editMode)
    {
        switch ($editMode) {
            case 'eshop_europe_order':
            case 'eshop_upcoming_crosscheck':
                $fieldToUpdate = 'eshop_europe_order';
                break;
            case 'category':
            case 'category_sim':
            case 'category_survival':
            case 'category_quiz':
            case 'category_spot_the_difference':
            case 'category_puzzle':
            case 'category_sports_racing':
            case 'category_hidden':
            case 'category_escape':
            case 'category_hentai_girls':
            case 'category_drone_flying_tour':
                $fieldToUpdate = 'category_id';
                break;
            default:
                abort(400);
        }

        return $fieldToUpdate;
    }

    public function bulkAdd()
    {
        $pageTitle = 'Bulk add games';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $bulkAddLimit = 20;

        $request = request();

        if ($request->isMethod('post')) {

            $errorTitles = [];

            $postData = $request->post();

            foreach ($postData as $pdk => $pdv) {

                if ($pdk == '_token') continue;

                for ($i=1; $i<$bulkAddLimit+1; $i++) {

                    $okToAdd = true;

                    $title       = $postData['title_'.$i];
                    $releaseDate = $postData['release_eu_'.$i];
                    $price       = $postData['price_'.$i];

                    $validator = Validator::make($request->all(), []);

                    // Add game
                    if ($title) {

                        // Check title hash is unique
                        $hashedTitle = $this->gameTitleHashGenerator->generateHash($title);
                        $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);

                        // Check for duplicates
                        if ($hashExists) {
                            $okToAdd = false;
                            $errorTitles[] = $title;
                        }

                        if (!$okToAdd) continue;

                        // OK to proceed
                        $linkTitle = $this->linkTitleGenerator->generate($title);
                        $gameBuilder = new GameBuilder();
                        $gameBuilder->setTitle($title);
                        $gameBuilder->setLinkTitle($linkTitle);
                        $gameBuilder->setReviewCount(0);
                        if ($releaseDate) {
                            $gameBuilder->setEuReleaseDate($releaseDate);
                        }
                        if ($price) {
                            $gameBuilder->setPriceEshop($price);
                        }

                        $game = $gameBuilder->getGame();
                        $game->save();
                        $gameId = $game->id;

                        // Add title hash
                        $this->repoGameTitleHash->create($title, $hashedTitle, $gameId);

                        // Add publisher, if selected
                        if (array_key_exists('publisher_id_'.$i, $postData)) {
                            $publisherId = $postData['publisher_id_'.$i];
                            $gamesCompany = $this->repoGamesCompany->find($publisherId);
                            if ($gamesCompany) {
                                $this->repoGamePublisher->create($gameId, $publisherId);
                                $this->gameQualityFilter->updateGame($game, $gamesCompany);
                            }
                        }

                    }

                }

                $errorsUrl = implode("|", $errorTitles);
                return redirect(route('staff.games.bulk-add.complete', ['errors' => $errorsUrl]));

            }

            // Done
            return redirect(route('staff.games.bulk-add.add'));

        }

        $bindings['BulkAddLimit'] = $bulkAddLimit;

        return view('staff.games.bulk-add.add', $bindings);
    }

    public function bulkAddComplete()
    {
        $pageTitle = 'Bulk add games - Complete';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $errors = request()->errors;
        if ($errors) {
            $errorsArray = explode("|", $errors);
            $bindings['Errors'] = $errorsArray;
        }

        return view('staff.games.bulk-add.complete', $bindings);
    }

    public function importFromCsv()
    {
        $pageTitle = 'Import games from CSV';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $importData = $request->import_data;

            $games = explode("\n", $importData);

            if (count($games) == 0) {
                return redirect(route('staff.games.import-from-csv.import'));
            }

            $errorTitles = [];

            foreach ($games as $gameRowRaw) {

                $okToAdd = true;

                $gameRow = explode("\t", $gameRowRaw);

                if (count($gameRow) < 6) abort(500);

                list($console, $title, $storeUrl, $releaseDate, $price, $imageUrl) = $gameRow;

                $imageUrl = trim($imageUrl);

                if (!$console) continue;
                if (!$title) continue;

                // Check title hash is unique
                $hashedTitle = $this->gameTitleHashGenerator->generateHash($title);
                $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);

                // Check for duplicates
                if ($hashExists) {
                    $okToAdd = false;
                    $errorTitles[] = $title;
                }

                if (!$okToAdd) continue;

                // OK to proceed
                $linkTitle = $this->linkTitleGenerator->generate($title);
                $gameBuilder = new GameBuilder();
                $gameBuilder->setTitle($title);
                $gameBuilder->setLinkTitle($linkTitle);
                if ($console == Console::DESC_SWITCH_2) {
                    $gameBuilder->setConsoleId(Console::ID_SWITCH_2);
                } else {
                    $gameBuilder->setConsoleId(Console::ID_SWITCH_1);
                }
                $gameBuilder->setReviewCount(0);
                if ($releaseDate) {
                    $carbonDate = Carbon::createFromFormat('d/m/Y', $releaseDate);
                    $releaseDateYMD = $carbonDate->format('Y-m-d');
                    $gameBuilder->setEuReleaseDate($releaseDateYMD);
                }
                if ($price) {
                    $gameBuilder->setPriceEshop($price);
                }
                if ($storeUrl) {
                    $gameBuilder->setNintendoStoreUrlOverride($storeUrl);
                }
                if ($imageUrl) {
                    $gameBuilder->setPackshotSquareUrlOverride($imageUrl);
                }

                $game = $gameBuilder->getGame();
                $game->save();
                $gameId = $game->id;

                // Add title hash
                $this->repoGameTitleHash->create($title, $hashedTitle, $gameId);

            }

            $errorsUrl = implode("|", $errorTitles);
            return redirect(route('staff.games.import-from-csv.complete', ['errors' => $errorsUrl]));

        }

        return view('staff.games.import-from-csv.import', $bindings);
    }

    public function importFromCsvComplete()
    {
        $pageTitle = 'Import games from CSV - Complete';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $errors = request()->errors;
        if ($errors) {
            $errorsArray = explode("|", $errors);
            $bindings['Errors'] = $errorsArray;
        }

        return view('staff.games.import-from-csv.complete', $bindings);
    }

    public function editList()
    {
        $pageTitle = 'Bulk edit games';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $request = request();
        $editMode = $request->editMode;
        $gameList = null;

        if ($request->routeIs('staff.games.bulk-edit.editPredefinedList')) {

            // This populates the game list from a DB query.

            $editModeList = [
                'eshop_upcoming_crosscheck',
                'category_sim',
                'category_survival',
                'category_quiz',
                'category_spot_the_difference',
                'category_puzzle',
                'category_sports_racing',
                'category_hidden',
                'category_escape',
                'category_hentai_girls',
                'category_drone_flying_tour',
            ];
            if (!in_array($editMode, $editModeList)) abort(404);

            $bindings = $this->getEditModeBindings($editMode, $bindings);

            switch ($editMode) {
                //case 'eshop_upcoming_crosscheck':
                    //$gameList = $this->repoGameLists->upcomingEshopCrosscheck();
                    //break;
                case 'category_sim':
                    $gameList = $this->repoGameListMissingCategory->simulation();
                    break;
                case 'category_survival':
                    $gameList = $this->repoGameListMissingCategory->survival();
                    break;
                case 'category_quiz':
                    $gameList = $this->repoGameListMissingCategory->quiz();
                    break;
                case 'category_spot_the_difference':
                    $gameList = $this->repoGameListMissingCategory->spotTheDifference();
                    break;
                case 'category_puzzle':
                    $gameList = $this->repoGameListMissingCategory->puzzle();
                    break;
                case 'category_sports_racing':
                    $gameList = $this->repoGameListMissingCategory->sportsAndRacing();
                    break;
                case 'category_hidden':
                    $gameList = $this->repoGameListMissingCategory->hidden();
                    break;
                case 'category_escape':
                    $gameList = $this->repoGameListMissingCategory->escape();
                    break;
                case 'category_hentai_girls':
                    $gameList = $this->repoGameListMissingCategory->hentaiGirls();
                    break;
                case 'category_drone_flying_tour':
                    $gameList = $this->repoGameListMissingCategory->droneFlyingTour();
                    break;
            }

        } else {

            // This uses a specific list of game IDs.

            $editModeList = [
                'category',
                'eshop_europe_order',
            ];
            if (!in_array($editMode, $editModeList)) abort(404);

            $bindings = $this->getEditModeBindings($editMode, $bindings);

            $gameIdList = $request->gameIdList;
            if (!$gameIdList) abort(404);
            $bindings['GameIdList'] = $gameIdList;

            $orderBy = '';
            if ($editMode == 'eshop_europe_order') {
                $orderBy = ['eshop_europe_order', 'asc'];
            }
            $gameList = $this->repoGame->getByIdList($gameIdList, $orderBy);

        }

        if (!$gameList) abort(404);

        if ($request->isMethod('post')) {

            //GameDirectorFactory::updateExisting($gameData, $request->post());

            $postData = $request->post();

            $fieldToUpdate = $this->getFieldToUpdate($editMode);

            foreach ($postData as $pdk => $pdv) {

                if ($pdk == '_token') continue;

                $gameId = str_replace($fieldToUpdate.'_', '', $pdk);
                $game = $this->repoGame->find($gameId);
                if (!$game) abort(400);

                $game->{$fieldToUpdate} = $pdv;
                $game->save();

                // Clear cache
                $this->repoGame->clearCacheCoreData($gameId);

            }

            // Done
            return redirect(route('staff.games.dashboard'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['EditMode'] = $editMode;
        $bindings['GameList'] = $gameList;

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();

        return view('staff.games.bulk-edit.edit-list', $bindings);
    }

}