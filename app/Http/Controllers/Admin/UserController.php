<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class UserController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userService = $serviceContainer->getUserService();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Users';
        $bindings['PanelTitle'] = 'Users';

        $userList = $userService->getAll();

        $bindings['UserList'] = $userList;

        return view('admin.user.list', $bindings);
    }

    public function showUser($userId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $userService = $serviceContainer->getUserService();
        $userListService = $serviceContainer->getUserListService();
        $userListItemService = $serviceContainer->getUserListItemService();

        $userData = $userService->find($userId);
        if (!$userData) abort(404);

        $displayName = $userData->display_name;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - View User - '.$displayName;
        $bindings['PanelTitle'] = 'View User - '.$displayName;

        $ownedList = $userListService->getOwnedListByUser($userId);
        $wishList = $userListService->getWishListByUser($userId);
        if ($ownedList) {
            $listId = $ownedList->id;
            //$bindings['OwnedList'] = $ownedList;
            $bindings['OwnedListItems'] = $userListItemService->getByList($listId);
        }
        if ($wishList) {
            $listId = $wishList->id;
            //$bindings['WishList'] = $wishList;
            $bindings['WishListItems'] = $userListItemService->getByList($listId);
        }

        $bindings['UserData'] = $userData;

        return view('admin.user.view', $bindings);
    }
}