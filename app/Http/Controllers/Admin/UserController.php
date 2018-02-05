<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class UserController extends \App\Http\Controllers\BaseController
{
    public function showList()
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Users';
        $bindings['PanelTitle'] = 'Users';

        $userService = resolve('Services\UserService');
        $userList = $userService->getAll();

        $bindings['UserList'] = $userList;

        return view('admin.user.list', $bindings);
    }
}