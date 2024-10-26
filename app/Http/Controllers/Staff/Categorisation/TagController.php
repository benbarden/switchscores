<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

class TagController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'tag_name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        private TagRepository $repoTag,
        private TagCategoryRepository $repoTagCategory
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Tags';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['TagList'] = $this->repoTag->getAll();

        return view('staff.categorisation.tag.list', $bindings);
    }

    public function addTag()
    {
        $pageTitle = 'Add tag';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationTagsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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
        $pageTitle = 'Edit tag';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->categorisationTagsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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

    public function deleteTag()
    {
        $currentUser = resolve('User/Repository')->currentUser();
        if (!$currentUser) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $tagId = $request->tagId;
        if (!$tagId) {
            return response()->json(['error' => 'Missing data: tagId'], 400);
        }

        $existingTag = $this->repoTag->find($tagId);
        if (!$existingTag) {
            return response()->json(['error' => 'Tag does not exist!'], 400);
        }

        if ($existingTag->gameTags()->count() > 0) {
            return response()->json(['error' => 'Found '.$existingTag->gameTags()->count().' game(s) with this tag'], 400);
        }

        $this->repoTag->delete($tagId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}