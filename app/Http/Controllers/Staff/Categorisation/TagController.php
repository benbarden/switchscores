<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

class TagController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

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
        $bindings = $this->getBindingsCategorisationSubpage('Tags');

        $bindings['TagList'] = $this->getServiceTag()->getAll();

        return view('staff.categorisation.tag.list', $bindings);
    }

    public function showGameTagList($gameId)
    {
        $game = $this->getServiceGame()->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = $this->getBindingsCategorisationTagSubpage('Tags for game: '.$gameTitle);

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameTagList'] = $this->getServiceGameTag()->getByGame($gameId);
        $bindings['UnusedTagList'] = $this->getServiceGameTag()->getTagsNotOnGame($gameId);

        return view('staff.categorisation.tag.gameTags', $bindings);
    }

    public function editTag($tagId)
    {
        $bindings = $this->getBindingsCategorisationTagSubpage('Edit tag');

        $tagData = $this->getServiceTag()->find($tagId);
        if (!$tagData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $tagName = $request->tag_name;
            $linkTitle = $request->link_title;
            $categoryId = $request->category_id;

            $this->getServiceTag()->edit($tagData, $tagName, $linkTitle, $categoryId);

            return redirect(route('staff.categorisation.tag.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TagData'] = $tagData;
        $bindings['TagId'] = $tagId;

        $bindings['CategoryList'] = $this->getServiceCategory()->getAll();

        return view('staff.categorisation.tag.edit', $bindings);
    }

    public function addGameTag()
    {
        $userId = $this->getAuthId();

        $user = $this->getServiceUser()->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $tagId = $request->tagId;
        if (!$tagId) {
            return response()->json(['error' => 'Missing data: tagId'], 400);
        }

        $tagData = $this->getServiceTag()->find($tagId);
        if (!$tagData) {
            return response()->json(['error' => 'Tag not found!'], 400);
        }

        $existingGameTag = $this->getServiceGameTag()->gameHasTag($gameId, $tagId);
        if ($existingGameTag) {
            return response()->json(['error' => 'Game already has this tag!'], 400);
        }

        $this->getServiceGameTag()->createGameTag($gameId, $tagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeGameTag()
    {
        $userId = $this->getAuthId();

        $user = $this->getServiceUser()->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        $gameTagId = $request->gameTagId;
        if (!$gameTagId) {
            return response()->json(['error' => 'Missing data: gameTagId'], 400);
        }

        $gameTagData = $this->getServiceGameTag()->find($gameTagId);
        if (!$gameTagData) {
            return response()->json(['error' => 'Game tag not found!'], 400);
        }

        if ($gameTagData->game_id != $gameId) {
            return response()->json(['error' => 'Game id mismatch on game tag record!'], 400);
        }

        $this->getServiceGameTag()->delete($gameTagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addTag()
    {
        $userId = $this->getAuthId();

        $user = $this->getServiceUser()->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $tagName = $request->tagName;
        if (!$tagName) {
            return response()->json(['error' => 'Missing data: tagName'], 400);
        }

        $existingTag = $this->getServiceTag()->getByName($tagName);
        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists!'], 400);
        }

        $linkTitle = $this->getServiceUrl()->generateLinkText($tagName);

        $this->getServiceTag()->create($tagName, $linkTitle);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function deleteTag()
    {
        $userId = $this->getAuthId();

        $user = $this->getServiceUser()->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $tagId = $request->tagId;
        if (!$tagId) {
            return response()->json(['error' => 'Missing data: tagId'], 400);
        }

        $existingTag = $this->getServiceTag()->find($tagId);
        if (!$existingTag) {
            return response()->json(['error' => 'Tag does not exist!'], 400);
        }

        if ($existingTag->gameTags()->count() > 0) {
            return response()->json(['error' => 'Found '.$existingTag->gameTags()->count().' game(s) with this tag'], 400);
        }

        $this->getServiceTag()->deleteTag($tagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}