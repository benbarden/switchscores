<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

use App\Factories\GameDirectorFactory;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\GameLists\Repository as GameListsRepository;

class BulkEditorController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    protected $repoGame;
    protected $repoGameLists;

    public function __construct(
        GameRepository $repoGame,
        GameListsRepository $repoGameLists
    )
    {
        $this->repoGame = $repoGame;
        $this->repoGameLists = $repoGameLists;
    }

    private function getEditModeBindings($editMode, $bindings)
    {
        $editModeHeader1 = '';
        $editModeHeader2 = '';
        $templateEditCell = '';
        $templateScripts = '';

        switch ($editMode) {
            case 'category':
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
                $fieldToUpdate = 'category_id';
                break;
            default:
                abort(400);
        }

        return $fieldToUpdate;
    }

    public function editList()
    {
        $bindings = $this->getBindingsGamesSubpage('Bulk edit games');

        $request = request();
        $editMode = $request->editMode;
        $gameList = null;

        if ($request->routeIs('staff.games.bulk-edit.editPredefinedList')) {

            // This populates the game list from a DB query.

            $editModeList = [
                'eshop_upcoming_crosscheck'
            ];
            if (!in_array($editMode, $editModeList)) abort(404);

            $bindings = $this->getEditModeBindings($editMode, $bindings);

            switch ($editMode) {
                case 'eshop_upcoming_crosscheck':
                    $gameList = $this->repoGameLists->upcomingEshopCrosscheck();
                    break;
            }

        } else {

            // This uses a specific list of game IDs.

            $editModeList = [
                'category',
                'eshop_europe_order'
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

        $bindings['CategoryList'] = $this->getServiceCategory()->getAllWithoutParents();

        /*
        $bindings['GameSeriesList'] = $this->getServiceGameSeries()->getAll();

        $bindings['FormatDigitalList'] = $this->getServiceGame()->getFormatOptionsDigital();
        $bindings['FormatPhysicalList'] = $this->getServiceGame()->getFormatOptionsPhysical();
        $bindings['FormatDLCList'] = $this->getServiceGame()->getFormatOptionsDLC();
        $bindings['FormatDemoList'] = $this->getServiceGame()->getFormatOptionsDemo();
        */

        return view('staff.games.bulk-edit.edit-list', $bindings);
    }

}