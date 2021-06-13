<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

use App\Factories\UserFactory;
use App\Factories\UserPointTransactionDirectorFactory;

use App\DbEditGame;
use App\Game;
use App\User;

class GameCategorySuggestionController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function show()
    {
        $request = request();
        $filterStatus = $request->filterStatus;

        if (!isset($filterStatus)) {
            $filterStatus = DbEditGame::STATUS_PENDING;
        }

        if ($filterStatus == DbEditGame::STATUS_PENDING) {
            $tableSort = "[0, 'asc']";
        } else {
            $tableSort = "[0, 'desc']";
        }

        $bindings = $this->getBindings('Game category suggestions');
        $this->setTableSort($tableSort);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage('Game category suggestions');

        $bindings['FilterStatus'] = $filterStatus;
        $dbEditList = $this->getServiceDbEditGame()->getCategoryEditsByStatus($filterStatus);

        $bindings['DbEditList'] = $dbEditList;
        $bindings['StatusList'] = $this->getServiceDbEditGame()->getStatusList();

        return view('staff.categorisation.game-category-suggestions.list', $bindings);
    }

    public function approve()
    {
        $request = request();

        $itemId = $request->itemId;
        if (!$itemId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }

        $dbEditGame = $this->getServiceDbEditGame()->find($itemId);
        if (!$dbEditGame) {
            return response()->json(['error' => 'Record not found: '.$itemId], 400);
        }

        $userId = $dbEditGame->user_id;
        $user = $this->getServiceUser()->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        if (!$dbEditGame->isPending()) {
            return response()->json(['error' => 'Record is not pending'], 400);
        }

        $game = $this->getServiceGame()->find($dbEditGame->game_id);
        if (!$game) {
            return response()->json(['error' => 'Game not found for this record'], 400);
        }

        // Approve the item
        $this->approveItem($dbEditGame, $game, $user);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function approveItem(DbEditGame $dbEditGame, Game $game, User $user)
    {
        // Make the change
        $game->category_id = $dbEditGame->new_data;
        $game->save();

        $gameId = $dbEditGame->game_id;

        // Credit points
        $userId = $user->id;
        UserFactory::addPointsForGameCategorySuggestion($user);

        // Store the transaction
        $userPointTransaction = UserPointTransactionDirectorFactory::addForGameCategorySuggestion($userId, $gameId);

        // Approve item
        $dbEditGame->setApproved();
        $dbEditGame->point_transaction_id = $userPointTransaction->id;
        $dbEditGame->save();
    }

    public function approveAll()
    {
        $dbEditList = $this->getServiceDbEditGame()->getPendingCategoryEdits();
        if (!$dbEditList) {
            return response()->json(['error' => 'No category edits to approve'], 400);
        }

        foreach ($dbEditList as $dbEditGame) {

            $userId = $dbEditGame->user_id;
            $user = $this->getServiceUser()->find($userId);
            $game = $this->getServiceGame()->find($dbEditGame->game_id);
            if ($user && $game) {
                $this->approveItem($dbEditGame, $game, $user);
            }

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function deny()
    {
        $serviceUser = $this->getServiceUser();
        $serviceDbEdit = $this->getServiceDbEditGame();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $itemId = $request->itemId;
        if (!$itemId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }

        $dbEditGame = $serviceDbEdit->find($itemId);
        if (!$dbEditGame) {
            return response()->json(['error' => 'Record not found: '.$itemId], 400);
        }

        if (!$dbEditGame->isPending()) {
            return response()->json(['error' => 'Record is not pending'], 400);
        }

        // Deny item
        $dbEditGame->setDenied();
        $dbEditGame->save();

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
