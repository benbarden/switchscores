<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use Auth;

class TagController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Tags';
        $bindings['PanelTitle'] = 'Tags';

        $tagService = $serviceContainer->getTagService();
        $bindings['TagList'] = $tagService->getAll();

        return view('admin.tag.list', $bindings);
    }

    public function addTag()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $tagService = $serviceContainer->getTagService();
        $userService = $serviceContainer->getUserService();

        $request = request();

        $tagName = $request->tagName;
        if (!$tagName) {
            return response()->json(['error' => 'Missing data: tagName'], 400);
        }

        $userId = Auth::id();

        $user = $userService->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $existingTag = $tagService->getByName($tagName);
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists!'], 400);
        }

        $tagService->create($tagName);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}