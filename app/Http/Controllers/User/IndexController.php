<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;
use Auth;

class IndexController extends Controller
{
    public function show()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Members index';
        $bindings['PageTitle'] = 'Members index';

        $bindings['UserRegion'] = Auth::user()->region;

        $userId = Auth::id();

        $userListService = $serviceContainer->getUserListService();
        $userListItemService = $serviceContainer->getUserListItemService();

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
