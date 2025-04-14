<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Construction\Game\GameBuilder;
use App\Construction\Game\GameDirector;

use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;
use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\FormatOptions as GameFormatOptions;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\Game\QualityFilter as GameQualityFilter;
use App\Domain\GamesCompany\Repository as GamesCompanyRepository;
use App\Domain\DataSource\NintendoCoUk\DownloadPackshotHelper;
use App\Domain\News\Repository as NewsRepository;
use App\Domain\GamePublisher\Repository as GamePublisherRepository;
use App\Domain\GameDeveloper\Repository as GameDeveloperRepository;
use App\Domain\DataSourceIgnore\Repository as DataSourceIgnoreRepository;
use App\Domain\DataSourceParsed\Repository as DataSourceParsedRepository;
use App\Domain\GameTag\Repository as GameTagRepository;
use App\Domain\Console\Repository as ConsoleRepository;

use App\Events\GameCreated;
use App\Factories\DataSource\NintendoCoUk\UpdateGameFactory;
use App\Factories\GameDirectorFactory;
use App\Models\Game;

use App\Services\Game\Images as GameImages;

use App\Traits\SwitchServices;

class GamesEditorController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:255',
        'link_title' => 'required|max:100',
        'console_id' => 'required',
        'price_eshop' => 'max:6',
        'players' => 'max:10',
        //'packshot_square_url_override' => 'required_with:nintendo_store_url_override'
    ];

    public function __construct(
        private GameTitleHashRepository $repoGameTitleHash,
        private HashGeneratorRepository $gameTitleHashGenerator,
        private GameSeriesRepository $repoGameSeries,
        private GameCollectionRepository $repoGameCollection,
        private CategoryRepository $repoCategory,
        private GameFormatOptions $formatOptions,
        private GameRepository $repoGame,
        private GameQualityFilter $gameQualityFilter,
        private GamesCompanyRepository $repoGamesCompany,
        private NewsRepository $repoNews,
        private GamePublisherRepository $repoGamePublisher,
        private GameDeveloperRepository $repoGameDeveloper,
        private DataSourceIgnoreRepository $repoDataSourceIgnore,
        private DataSourceParsedRepository $repoDataSourceParsed,
        private GameTagRepository $repoGameTag,
        private ConsoleRepository $repoConsole
    )
    {
    }

    public function add()
    {
        $pageTitle = 'Add game';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.games.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Check title hash is unique
            $hashedTitle = $this->gameTitleHashGenerator->generateHash($request->title);
            $hashExists = $this->repoGameTitleHash->titleHashExists($hashedTitle);

            $validator->after(function ($validator) use ($hashExists) {
                // Check for duplicates
                if ($hashExists) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.games.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // Add game
            $gameDirector = new GameDirector();
            $gameBuilder = new GameBuilder();
            $gameDirector->setBuilder($gameBuilder);
            $gameDirector->buildNewGame($request->post());
            $game = $gameBuilder->getGame();
            $game->save();
            $gameId = $game->id;

            // Add title hash
            $this->repoGameTitleHash->create($request->title, $hashedTitle, $gameId);

            // Add publisher, if selected
            if ($request->publisher_id) {
                $publisherId = $request->publisher_id;
                $gamesCompany = $this->repoGamesCompany->find($publisherId);
                if ($gamesCompany) {
                    $this->repoGamePublisher->create($gameId, $request->publisher_id);
                    $this->gameQualityFilter->updateGame($game, $gamesCompany);
                }
            }

            // Check eu_released_on
            if ($request->eu_is_released == 1) {
                $dateNow = new \DateTime('now');
                $game->eu_released_on = $dateNow->format('Y-m-d H:i:s');
                $game->save();
            }

            // Done

            // Trigger event
            event(new GameCreated($game));

            return redirect('/staff/games/detail/'.$gameId.'?lastaction=add&lastgameid='.$gameId);

        }

        $bindings['FormMode'] = 'add';

        $bindings['ConsoleList'] = $this->repoConsole->consoleList();
        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();
        $bindings['GameSeriesList'] = $this->repoGameSeries->getAll();
        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['FormatDigitalList'] = $this->formatOptions->getOptionsDigital();
        $bindings['FormatPhysicalList'] = $this->formatOptions->getOptionsPhysical();
        $bindings['FormatDLCList'] = $this->formatOptions->getOptionsDLC();
        $bindings['FormatDemoList'] = $this->formatOptions->getOptionsDemo();

        return view('staff.games.editor.add', $bindings);
    }

    public function edit($gameId)
    {
        $pageTitle = 'Edit game';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';
            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.games.edit', ['gameId' => $gameId]))
                    ->withErrors($validator)
                    ->withInput();
            }

            GameDirectorFactory::updateExisting($gameData, $request->post());

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

        $bindings['ConsoleList'] = $this->repoConsole->consoleList();
        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();
        $bindings['GameSeriesList'] = $this->repoGameSeries->getAll();
        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['FormatDigitalList'] = $this->formatOptions->getOptionsDigital();
        $bindings['FormatPhysicalList'] = $this->formatOptions->getOptionsPhysical();
        $bindings['FormatDLCList'] = $this->formatOptions->getOptionsDLC();
        $bindings['FormatDemoList'] = $this->formatOptions->getOptionsDemo();

        return view('staff.games.editor.edit', $bindings);
    }

    public function editNintendoCoUk($gameId)
    {
        $pageTitle = 'Edit Nintendo.co.uk link';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        $game = $this->repoGame->find($gameId);
        if (!$game) abort(404);

        $dsCurrentParsedItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $nintendoCoUkLinkId = $request->nintendo_co_uk_link_id;

            if ($nintendoCoUkLinkId) {
                $dsNewParsedItem = $this->repoDataSourceParsed->getNintendoCoUkByLinkId($nintendoCoUkLinkId);
                if (!$dsNewParsedItem) abort(500);
            } else {
                $dsNewParsedItem = null;
            }

            if ($dsCurrentParsedItem) {

                // If game is currently linked to a different API item, we need to break the link
                if ($nintendoCoUkLinkId != $dsCurrentParsedItem->link_id) {

                    $dsCurrentParsedItem->game_id = null;
                    $dsCurrentParsedItem->save();

                    // We also need to delete any current packshots
                    $serviceGameImages = new GameImages($game);
                    $serviceGameImages->deleteSquare();
                    $serviceGameImages->deleteHeader();

                    // Reset the game
                    $game->image_square = null;
                    $game->image_header = null;
                    $game->eshop_europe_fs_id = null;
                    $game->save();

                }

            }

            if ($dsNewParsedItem) {

                if ($game->eshop_europe_fs_id != $dsNewParsedItem->link_id) {

                    // Link the game to the parsed item (not sure we need a two-way link?)
                    $game->eshop_europe_fs_id = $dsNewParsedItem->link_id;

                    // Update availability
                    $game->format_digital = Game::FORMAT_AVAILABLE;

                    $game->save();

                }

                // Link the parsed item to the game
                $dsNewParsedItem->game_id = $gameId;
                $dsNewParsedItem->save();

                UpdateGameFactory::doUpdate($game, $dsNewParsedItem);

                // Download packshots
                $downloadPackshotHelper = new DownloadPackshotHelper();
                $downloadPackshotHelper->downloadForGame($game);

            } else {

                // Clear digital status - Removed, we can keep the previous value.
                //$game->format_digital = null;
                // Clear fs_id
                $game->eshop_europe_fs_id = null;
                $game->save();

            }

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameData'] = $game;
        $bindings['GameId'] = $gameId;

        $ignoreIdList = $this->repoDataSourceIgnore->getNintendoCoUkLinkIdList();
        $dsNintendoCoUkParsedList = $this->repoDataSourceParsed->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        if ($dsCurrentParsedItem) {
            $dsNintendoCoUkParsedList->prepend($dsCurrentParsedItem);
        }
        $bindings['DSNintendoCoUkParsedList'] = $dsNintendoCoUkParsedList;

        $dsParsedItem = $this->repoDataSourceParsed->getSourceNintendoCoUkForGame($gameId);
        if ($dsParsedItem) {
            $bindings['DSParsedItem'] = $dsParsedItem;
        }

        return view('staff.games.editor.nintendo-co-uk.edit', $bindings);
    }

    public function delete($gameId)
    {
        $pageTitle = 'Delete game';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gameNews = $this->repoNews->getByGameId($gameId);
        if (count($gameNews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameNews).' news article(s)';
        }

        $gameReviews = $this->getServiceReviewLink()->getByGame($gameId);
        if (count($gameReviews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameReviews).' review(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServiceGameTitleHash()->deleteByGameId($gameId);
            $this->repoGameTag->deleteByGameId($gameId);
            $this->repoGameDeveloper->deleteByGameId($gameId);
            $this->repoGamePublisher->deleteByGameId($gameId);
            // Game import rule cleanup
            $this->getServiceGameImportRuleEshop()->deleteByGameId($gameId);
            // Image cleanup
            $serviceGameImages = new GameImages($gameData);
            $serviceGameImages->deleteSquare();
            $serviceGameImages->deleteHeader();
            // Data source cleanup
            $this->repoDataSourceParsed->clearGameIdFromNintendoCoUkItems($gameId);
            $this->repoDataSourceParsed->removeSwitchEshopItems($gameId);
            // Ready to delete the game
            $this->repoGame->delete($gameId);

            // Done

            return redirect(route('staff.games.dashboard'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;
        $bindings['ErrorsCustom'] = $customErrors;

        return view('staff.games.editor.delete', $bindings);
    }

    public function releaseGame()
    {
        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $this->repoGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        $this->repoGame->markAsReleased($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}