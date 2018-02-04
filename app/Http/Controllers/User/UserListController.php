<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use Auth;

use App\UserListItem;
use App\Services\UserListService;
use App\Services\UserListItemService;

class UserListController extends BaseController
{
    public function addPlaylistItem()
    {
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

        $userListService = resolve('Services\UserListService');
        $userListItemService = resolve('Services\UserListItemService');
        /* @var $userListService UserListService */
        /* @var $userListItemService UserListItemService */

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
        $request = request();

        $playlistItemId = $request->itemId;

        if (!$playlistItemId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }

        $serviceUserListItem = resolve('Services\UserListItemService');
        /* @var $serviceUserListItem UserListItemService */
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
