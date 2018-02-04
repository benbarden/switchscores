<?php

namespace App\Http\Controllers\User;

use Auth;
use App\Services\UserListService;
use App\Services\UserListItemService;

class IndexController extends \App\Http\Controllers\BaseController
{
    public function show()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Members index';
        $bindings['PanelTitle'] = 'Members index';

        $userId = Auth::id();

        $userListService = resolve('Services\UserListService');
        $userListItemService = resolve('Services\UserListItemService');
        /* @var $userListService UserListService */
        /* @var $userListItemService UserListItemService */

        $ownedList = $userListService->getOwnedListByUser($userId);
        $wishList = $userListService->getWishListByUser($userId);

        if ($ownedList) {
            $listId = $ownedList->id;
            $bindings['OwnedList'] = $ownedList;
            $bindings['OwnedListItems'] = $userListItemService->getByList($listId);
        }
        if ($wishList) {
            $listId = $wishList->id;
            $bindings['WishList'] = $wishList;
            $bindings['WishListItems'] = $userListItemService->getByList($listId);
        }

        return view('user.index', $bindings);
    }
}
