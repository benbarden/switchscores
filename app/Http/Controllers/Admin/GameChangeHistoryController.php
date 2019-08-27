<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use App\GameChangeHistory;

class GameChangeHistoryController extends Controller
{
    public function index($filter = "")
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $filterGameId = \Request::get('gameId');

        $bindings = [];

        if ($filterGameId) {

            $game = $serviceGame->find($filterGameId);
            if (!$game) abort(404);

            $changeHistory = $serviceContainer->getGameChangeHistoryService()->getByGameId($filterGameId);
            $bindings['FilterGame'] = $game;

        } else {

            switch ($filter) {
                case 'table-games':
                    $changeHistory = $serviceContainer->getGameChangeHistoryService()->getByTable(GameChangeHistory::TABLE_NAME_GAMES);
                    break;
                case 'source-eshop-europe':
                    $changeHistory = $serviceContainer->getGameChangeHistoryService()->getBySource(GameChangeHistory::SOURCE_ESHOP_EUROPE);
                    break;
                case 'source-wikipedia':
                    $changeHistory = $serviceContainer->getGameChangeHistoryService()->getBySource(GameChangeHistory::SOURCE_WIKIPEDIA);
                    break;
                case 'source-admin':
                    $changeHistory = $serviceContainer->getGameChangeHistoryService()->getBySource(GameChangeHistory::SOURCE_ADMIN);
                    break;
                default:
                    $tableLimit = 250;
                    $changeHistory = $serviceContainer->getGameChangeHistoryService()->getAll($tableLimit);
                    $bindings['TableLimit'] = $tableLimit;
                    break;
            }

        }

        $bindings['TopTitle'] = 'Game change history';
        $bindings['PageTitle'] = 'Game change history';

        $bindings['ItemList'] = $changeHistory;
        $bindings['jsInitialSort'] = "[ 0, 'desc']";
        if ($filter) {
            $bindings['SelectedFilter'] = $filter;
        }

        return view('admin.game-change-history.index', $bindings);
    }
}
