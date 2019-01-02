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
        $collectionService = $serviceContainer->getUserGamesCollectionService();

        $userData = $userService->find($userId);
        if (!$userData) abort(404);

        $displayName = $userData->display_name;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - View User - '.$displayName;
        $bindings['PanelTitle'] = 'View User - '.$displayName;

        $bindings['UserData'] = $userData;

        $bindings['CollectionList'] = $collectionService->getByUser($userId);
        $bindings['CollectionStats'] = $collectionService->getStats($userId);

        return view('admin.user.view', $bindings);
    }
}