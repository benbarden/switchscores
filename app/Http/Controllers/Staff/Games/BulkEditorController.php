<?php

namespace App\Http\Controllers\Staff\Games;

use App\Domain\Category\Repository as CategoryRepository;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;

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

use App\Traits\SwitchServices;

use App\Factories\GameDirectorFactory;

class BulkEditorController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function __construct(
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

    public function eshopUpcomingCrosscheck()
    {
        $pageTitle = 'Bulk edit games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $gameList = $this->repoGameLists->upcomingEshopCrosscheck();
        $bindings['GameList'] = $gameList;

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
            case 'category_puzzle':
            case 'category_sports_racing':
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
            case 'category_puzzle':
            case 'category_sports_racing':
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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $errors = request()->errors;
        if ($errors) {
            $errorsArray = explode("|", $errors);
            $bindings['Errors'] = $errorsArray;
        }

        return view('staff.games.bulk-add.complete', $bindings);
    }

    public function editList()
    {
        $pageTitle = 'Bulk edit games';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();
        $editMode = $request->editMode;
        $gameList = null;

        if ($request->routeIs('staff.games.bulk-edit.editPredefinedList')) {

            // This populates the game list from a DB query.

            $editModeList = [
                'eshop_upcoming_crosscheck',
                'category_sim',
                'category_puzzle',
                'category_sports_racing',
            ];
            if (!in_array($editMode, $editModeList)) abort(404);

            $bindings = $this->getEditModeBindings($editMode, $bindings);

            switch ($editMode) {
                case 'eshop_upcoming_crosscheck':
                    $gameList = $this->repoGameLists->upcomingEshopCrosscheck();
                    break;
                case 'category_sim':
                    $gameList = $this->repoGameListMissingCategory->simulation();
                    break;
                case 'category_puzzle':
                    $gameList = $this->repoGameListMissingCategory->puzzle();
                    break;
                case 'category_sports_racing':
                    $gameList = $this->repoGameListMissingCategory->sportsAndRacing();
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