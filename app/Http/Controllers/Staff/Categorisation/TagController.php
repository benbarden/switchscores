<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\StaffView;

use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

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

    protected $viewBreadcrumbs;
    private $repoTag;
    private $repoTagCategory;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs,
        TagRepository $repoTag,
        TagCategoryRepository $repoTagCategory
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
        $this->repoTag = $repoTag;
        $this->repoTagCategory = $repoTagCategory;
    }

    public function showList()
    {
        $bindings = $this->getBindings('Tags');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage('Tags');

        $bindings['TagList'] = $this->repoTag->getAll();

        return view('staff.categorisation.tag.list', $bindings);
    }

    public function showGameTagList($gameId)
    {
        $game = $this->getServiceGame()->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = $this->getBindings('Tags for game: '.$gameTitle);
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationTagsSubpage('Tags for game: '.$gameTitle);

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameTagList'] = $this->getServiceGameTag()->getByGame($gameId);
        $bindings['UnusedTagList'] = $this->getServiceGameTag()->getTagsNotOnGame($gameId);

        return view('staff.categorisation.tag.gameTags', $bindings);
    }

    public function addTag()
    {
        $bindings = $this->getBindings('Add tag');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationTagsSubpage('Add tag');

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'add-post';

            $this->validate($request, $this->validationRules);

            $tagName = $request->tag_name;
            $linkTitle = $request->link_title;
            $tagCategoryId = $request->tag_category_id;

            $this->repoTag->create($tagName, $linkTitle, $tagCategoryId);

            return redirect(route('staff.categorisation.tag.list'));

        } else {

            $bindings['FormMode'] = 'add';

        }

        $bindings['CategoryList'] = $this->repoTagCategory->getAll();

        return view('staff.categorisation.tag.add', $bindings);
    }

    public function editTag($tagId)
    {
        $bindings = $this->getBindings('Edit tag');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationTagsSubpage('Edit tag');

        $tagData = $this->repoTag->find($tagId);
        if (!$tagData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $tagName = $request->tag_name;
            $linkTitle = $request->link_title;
            $tagCategoryId = $request->tag_category_id;

            $this->repoTag->edit($tagData, $tagName, $linkTitle, $tagCategoryId);

            return redirect(route('staff.categorisation.tag.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TagData'] = $tagData;
        $bindings['TagId'] = $tagId;

        $bindings['CategoryList'] = $this->repoTagCategory->getAll();

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