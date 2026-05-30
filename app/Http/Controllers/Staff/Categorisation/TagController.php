<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Domain\LayoutVersion\Helper as LayoutVersionHelper;

class TagController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesBase = [
        'tag_name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private TagRepository $repoTag,
        private TagCategoryRepository $repoTagCategory,
        private LayoutVersionHelper $helperLayoutVersion,
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'Tags';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationSubpage($pageTitle))->bindings;

        $tagCategoryList = $this->repoTagCategory->getAll();
        $tagList = new Collection();
        foreach ($tagCategoryList as $tagCategory) {
            $tagCategoryId = $tagCategory->id;
            $tagsInCategory = $this->repoTag->getByTagCategory($tagCategoryId);
            foreach ($tagsInCategory as $tempItem) {
                $tagList->add($tempItem);
            }
        }
        $bindings['TagList'] = $tagList;

        return view('staff.categorisation.tag.list', $bindings);
    }

    public function addTag(Request $request)
    {
        $pageTitle = 'Add tag';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationTagsSubpage($pageTitle))->bindings;

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'add-post';

            $rules = array_merge($this->validationRulesBase, [
                'link_title' => 'required|unique:tags,link_title',
            ]);
            $this->validate($request, $rules);

            $tagName = $request->tag_name;
            $linkTitle = $request->link_title;
            $tagCategoryId = $request->tag_category_id;
            $taxonomyReviewed = $request->taxonomy_reviewed;
            $layoutVersion = $request->layout_version;
            $metaDescription = $request->meta_description;
            $introDescription = $request->intro_description;

            $this->repoTag->create($tagName, $linkTitle, $tagCategoryId, $taxonomyReviewed,
                $layoutVersion, $metaDescription, $introDescription);

            return redirect(route('staff.categorisation.tag.list'));

        } else {

            $bindings['FormMode'] = 'add';

        }

        $bindings['CategoryList'] = $this->repoTagCategory->getAll();
        $bindings['LayoutVersionList'] = $this->helperLayoutVersion->buildList();

        return view('staff.categorisation.tag.add', $bindings);
    }

    public function editTag(Request $request, $tagId)
    {
        $pageTitle = 'Edit tag';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationTagsSubpage($pageTitle))->bindings;

        $tagData = $this->repoTag->find($tagId);
        if (!$tagData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $rules = array_merge($this->validationRulesBase, [
                'link_title' => 'required|unique:tags,link_title,'.$tagId,
            ]);
            $this->validate($request, $rules);

            $tagName = $request->tag_name;
            $linkTitle = $request->link_title;
            $tagCategoryId = $request->tag_category_id;
            $taxonomyReviewed = $request->taxonomy_reviewed;
            $layoutVersion = $request->layout_version;
            $metaDescription = $request->meta_description;
            $introDescription = $request->intro_description;

            $this->repoTag->edit($tagData, $tagName, $linkTitle, $tagCategoryId, $taxonomyReviewed,
                $layoutVersion, $metaDescription, $introDescription);

            return redirect(route('staff.categorisation.tag.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TagData'] = $tagData;
        $bindings['TagId'] = $tagId;

        $bindings['CategoryList'] = $this->repoTagCategory->getAll();
        $bindings['LayoutVersionList'] = $this->helperLayoutVersion->buildList();

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