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

use App\Events\GameCreated;
use App\Factories\DataSource\NintendoCoUk\DownloadImageFactory;
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
        'price_eshop' => 'max:6',
        'players' => 'max:10',
    ];

    private $repoGameTitleHash;
    private $gameTitleHashGenerator;
    private $repoGameSeries;
    private $repoGameCollection;
    private $repoCategory;
    private $formatOptions;
    private $repoGame;

    public function __construct(
        GameTitleHashRepository $repoGameTitleHash,
        HashGeneratorRepository $gameTitleHashGenerator,
        GameSeriesRepository $repoGameSeries,
        GameCollectionRepository $repoGameCollection,
        CategoryRepository $repoCategory,
        GameFormatOptions $formatOptions,
        GameRepository $repoGame
    )
    {
        $this->repoGameTitleHash = $repoGameTitleHash;
        $this->gameTitleHashGenerator = $gameTitleHashGenerator;
        $this->repoGameSeries = $repoGameSeries;
        $this->repoGameCollection = $repoGameCollection;
        $this->repoCategory = $repoCategory;
        $this->formatOptions = $formatOptions;
        $this->repoGame = $repoGame;
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

            $this->validate($request, $this->validationRules);

            GameDirectorFactory::updateExisting($gameData, $request->post());

            // Done
            return redirect('/staff/games/detail/'.$gameId.'?lastaction=edit&lastgameid='.$gameId);

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['GameData'] = $gameData;
        $bindings['GameId'] = $gameId;

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

        $dsCurrentParsedItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $nintendoCoUkLinkId = $request->nintendo_co_uk_link_id;

            if ($nintendoCoUkLinkId) {
                $dsNewParsedItem = $this->getServiceDataSourceParsed()->getNintendoCoUkByLinkId($nintendoCoUkLinkId);
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
                DownloadImageFactory::downloadImages($game, $dsNewParsedItem);

            } else {

                // Clear digital status
                $game->format_digital = null;
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

        $ignoreIdList = $this->getServiceDataSourceIgnore()->getNintendoCoUkLinkIdList();
        $dsNintendoCoUkParsedList = $this->getServiceDataSourceParsed()->getAllNintendoCoUkWithNoGameId($ignoreIdList);
        if ($dsCurrentParsedItem) {
            $dsNintendoCoUkParsedList->prepend($dsCurrentParsedItem);
        }
        $bindings['DSNintendoCoUkParsedList'] = $dsNintendoCoUkParsedList;

        $dsParsedItem = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);
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

        // Core
        $serviceGame = $this->getServiceGame();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        // Categorisation
        $serviceGameTag = $this->getServiceGameTag();

        // Validation
        $serviceNews = $this->getServiceNews();
        $serviceReviewLink = $this->getServiceReviewLink();

        // Deletion
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();

        $gameData = $this->repoGame->find($gameId);
        if (!$gameData) abort(404);

        $customErrors = [];

        $request = request();

        // Validation: check for any reason we should not allow the game to be deleted.
        $gameNews = $serviceNews->getByGameId($gameId);
        if (count($gameNews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameNews).' news article(s)';
        }

        $gameReviews = $serviceReviewLink->getByGame($gameId);
        if (count($gameReviews) > 0) {
            $customErrors[] = 'Game is linked to '.count($gameReviews).' review(s)';
        }

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceGameTitleHash->deleteByGameId($gameId);
            $serviceGameTag->deleteGameTags($gameId);
            $serviceGameDeveloper->deleteByGameId($gameId);
            $serviceGamePublisher->deleteByGameId($gameId);
            // Game import rule cleanup
            $this->getServiceGameImportRuleEshop()->deleteByGameId($gameId);
            // Image cleanup
            $serviceGameImages = new GameImages($gameData);
            $serviceGameImages->deleteSquare();
            $serviceGameImages->deleteHeader();
            // Data source cleanup
            $this->getServiceDataSourceParsed()->clearGameIdFromNintendoCoUkItems($gameId);
            $this->getServiceDataSourceParsed()->removeSwitchEshopItems($gameId);
            // Ready to delete the game
            $serviceGame->deleteGame($gameId);

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
        $serviceGame = $this->getServiceGame();

        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Game not found: '.$gameId], 400);
        }

        $serviceGame->markAsReleased($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}