<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;

class TagController extends Controller
{
    use SwitchServices;
    use AuthUser;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'tag_name' => 'required',
        'link_title' => 'required',
    ];

    public function showList()
    {
        $serviceTag = $this->getServiceTag();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Tags';
        $bindings['PageTitle'] = 'Tags';

        $bindings['TagList'] = $serviceTag->getAll();

        return view('staff.categorisation.tag.list', $bindings);
    }

    public function showGameTagList($gameId)
    {
        $serviceGame = $this->getServiceGame();
        $serviceGameTag = $this->getServiceGameTag();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Tags for game: '.$gameTitle;
        $bindings['PageTitle'] = 'Tags for game: '.$gameTitle;

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameTagList'] = $serviceGameTag->getByGame($gameId);

        $bindings['UnusedTagList'] = $serviceGameTag->getTagsNotOnGame($gameId);

        return view('staff.categorisation.tag.gameTags', $bindings);
    }

    public function editTag($tagId)
    {
        $serviceTag = $this->getServiceTag();
        $servicePrimaryType = $this->getServiceGamePrimaryType();

        $tagData = $serviceTag->find($tagId);
        if (!$tagData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $tagName = $request->tag_name;
            $linkTitle = $request->link_title;
            $primaryTypeId = $request->primary_type_id;

            $serviceTag->edit($tagData, $tagName, $linkTitle, $primaryTypeId);

            return redirect(route('staff.categorisation.tag.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Edit tag';
        $bindings['PageTitle'] = 'Edit tag';
        $bindings['TagData'] = $tagData;
        $bindings['TagId'] = $tagId;

        $bindings['PrimaryTypeList'] = $servicePrimaryType->getAll();

        return view('staff.categorisation.tag.edit', $bindings);
    }

    public function addGameTag()
    {
        $serviceTag = $this->getServiceTag();
        $serviceUser = $this->getServiceUser();
        $serviceGameTag = $this->getServiceGameTag();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $tagId = $request->tagId;
        if (!$tagId) {
            return response()->json(['error' => 'Missing data: tagId'], 400);
        }

        $tagData = $serviceTag->find($tagId);
        if (!$tagData) {
            return response()->json(['error' => 'Tag not found!'], 400);
        }

        $existingGameTag = $serviceGameTag->gameHasTag($gameId, $tagId);
        if ($existingGameTag) {
            return response()->json(['error' => 'Game already has this tag!'], 400);
        }

        $serviceGameTag->createGameTag($gameId, $tagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGameTag()
    {
        $serviceUser = $this->getServiceUser();
        $serviceGameTag = $this->getServiceGameTag();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $gameTagId = $request->gameTagId;
        if (!$gameTagId) {
            return response()->json(['error' => 'Missing data: gameTagId'], 400);
        }

        $gameTagData = $serviceGameTag->find($gameTagId);
        if (!$gameTagData) {
            return response()->json(['error' => 'Game tag not found!'], 400);
        }

        if ($gameTagData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game tag record!'], 400);
        }

        $serviceGameTag->delete($gameTagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addTag()
    {
        $serviceUser = $this->getServiceUser();
        $serviceTag = $this->getServiceTag();
        $serviceUrl = $this->getServiceUrl();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $tagName = $request->tagName;
        if (!$tagName) {
            return response()->json(['error' => 'Missing data: tagName'], 400);
        }

        $existingTag = $serviceTag->getByName($tagName);
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists!'], 400);
        }

        $linkTitle = $serviceUrl->generateLinkText($tagName);

        $serviceTag->create($tagName, $linkTitle);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function deleteTag()
    {
        $serviceUser = $this->getServiceUser();
        $serviceTag = $this->getServiceTag();

        $userId = $this->getAuthId();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $tagId = $request->tagId;
        if (!$tagId) {
            return response()->json(['error' => 'Missing data: tagId'], 400);
        }

        $existingTag = $serviceTag->find($tagId);
        if (!$existingTag) {
            return response()->json(['error' => 'Tag does not exist!'], 400);
        }

        if ($existingTag->gameTags()->count() > 0) {
            return response()->json(['error' => 'Found '.$existingTag->gameTags()->count().' game(s) with this tag'], 400);
        }

        $serviceTag->deleteTag($tagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}