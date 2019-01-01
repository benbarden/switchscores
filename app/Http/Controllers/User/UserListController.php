<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class UserListController extends Controller
{
    public function addPlaylistItem()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userListService = $serviceContainer->getUserListService();
        $userListItemService = $serviceContainer->getUserListItemService();

        $request = request();

        $gameId = $request->itemId;
        $itemNewState = $request->itemNewState;
        $listType = $request->listType;

        if (!$gameId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }
        if (!in_array($itemNewState, ['added', 'not-added'])) {
            return response()->json(['error' => 'Invalid data for itemNewState'], 400);
        }
        if (!in_array($listType, ['owned', 'wish'])) {
            return response()->json(['error' => 'Invalid data for listType'], 400);
        }

        $userId = Auth::id();

        if ($listType == 'owned') {

            $ownedList = $userListService->getOwnedListByUser($userId);
            $listId = $ownedList->id;
            $gameOnList = $userListItemService->getByListAndGame($listId, $gameId);

        } elseif ($listType == 'wish') {

            $wishList = $userListService->getWishListByUser($userId);
            $listId = $wishList->id;
            $gameOnList = $userListItemService->getByListAndGame($listId, $gameId);

        }

        if (($itemNewState == 'added') && (!is_null($gameOnList))) {
            return response()->json(['error' => 'Game already on list'], 400);
        } elseif (($itemNewState == 'not-added') && (is_null($gameOnList))) {
            return response()->json(['error' => 'Game already removed from list'], 400);
        }

        if ($itemNewState == 'added') {
            // Add to list!
            $newUserListItem = new UserListItem();
            $newUserListItem->list_id = $listId;
            $newUserListItem->game_id = $gameId;
            $newUserListItem->save();
        } elseif ($itemNewState == 'not-added') {
            // Remove from list!
            $gameOnList->delete();
        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function deletePlaylistItem()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceUserListItem = $serviceContainer->getUserListItemService();

        $request = request();

        $playlistItemId = $request->itemId;

        if (!$playlistItemId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }

        $userListItem = $serviceUserListItem->find($playlistItemId);

        if ($userListItem->listParent->user_id != Auth::id()) {
            return response()->json(['error' => 'Playlist belongs to another user'], 400);
        }

        // Delete from playlist
        $userListItem->delete();

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
