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
        $bindings['PageTitle'] = 'Tags';

        $tagService = $serviceContainer->getTagService();
        $bindings['TagList'] = $tagService->getAll();

        return view('admin.tag.list', $bindings);
    }

    public function showGameTagList($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $gameService = $serviceContainer->getGameService();
        $gameTagService = $serviceContainer->getGameTagService();

        $game = $gameService->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Tags for game: '.$gameTitle;
        $bindings['PageTitle'] = 'Tags for game: '.$gameTitle;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameTagList'] = $gameTagService->getByGame($gameId);

        $bindings['UnusedTagList'] = $gameTagService->getTagsNotOnGame($gameId);

        return view('admin.tag.gameTags', $bindings);
    }

    public function addGameTag()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $tagService = $serviceContainer->getTagService();
        $gameTagService = $serviceContainer->getGameTagService();
        $userService = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $userService->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $tagId = $request->tagId;
        if (!$tagId) {
            return response()->json(['error' => 'Missing data: tagId'], 400);
        }

        $tagData = $tagService->find($tagId);
        if (!$tagData) {
            return response()->json(['error' => 'Tag not found!'], 400);
        }

        $existingGameTag = $gameTagService->gameHasTag($gameId, $tagId);
        if ($existingGameTag) {
            return response()->json(['error' => 'Game already has this tag!'], 400);
        }

        $gameTagService->createGameTag($gameId, $tagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGameTag()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $tagService = $serviceContainer->getTagService();
        $gameTagService = $serviceContainer->getGameTagService();
        $userService = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $userService->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $gameTagId = $request->gameTagId;
        if (!$gameTagId) {
            return response()->json(['error' => 'Missing data: gameTagId'], 400);
        }

        $gameTagData = $gameTagService->find($gameTagId);
        if (!$gameTagData) {
            return response()->json(['error' => 'Game tag not found!'], 400);
        }

        if ($gameTagData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game tag record!'], 400);
        }

        $gameTagService->delete($gameTagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addTag()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $tagService = $serviceContainer->getTagService();
        $userService = $serviceContainer->getUserService();
        $urlService = $serviceContainer->getUrlService();

        $userId = Auth::id();

        $user = $userService->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $tagName = $request->tagName;
        if (!$tagName) {
            return response()->json(['error' => 'Missing data: tagName'], 400);
        }

        $existingTag = $tagService->getByName($tagName);
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists!'], 400);
        }

        $linkTitle = $urlService->generateLinkText($tagName);

        $tagService->create($tagName, $linkTitle);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}