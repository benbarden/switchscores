<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\MissingCategory as GameListMissingCategoryRepository;

class BulkEditorController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory,
        private GameRepository $repoGame,
        private GameListsRepository $repoGameLists,
        private GameListMissingCategoryRepository $repoGameListMissingCategory,
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