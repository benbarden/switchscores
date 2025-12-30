<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Category\Blurb as CategoryBlurb;
use App\Domain\LayoutVersion\Helper as LayoutVersionHelper;

class CategoryController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory,
        private CategoryBlurb $blurbCategory,
        private LayoutVersionHelper $helperLayoutVersion,
    )
    {

    }

    public function showList()
    {
        $pageTitle = 'Categories';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationSubpage($pageTitle))->bindings;

        $fullCategoryList = [];
        $topLevelCategories = $this->repoCategory->topLevelCategories();
        foreach ($topLevelCategories as $categoryItem) {
            $fullCategoryList[] = $categoryItem;
            $categoryChildren = $this->repoCategory->categoryChildren($categoryItem->id);
            foreach ($categoryChildren as $categoryChild) {
                $fullCategoryList[] = $categoryChild;
            }
        }
        $bindings['CategoryList'] = $fullCategoryList;

        return view('staff.categorisation.category.list', $bindings);
    }

    public function addCategory(Request $request)
    {
        $pageTitle = 'Add category';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationCategoriesSubpage($pageTitle))->bindings;

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.category.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingCategory = $this->repoCategory->getByName($request->name);

            $validator->after(function ($validator) use ($existingCategory) {
                // Check for duplicates
                if ($existingCategory != null) {
                    $validator->errors()->add('title', 'Title already exists for another record!');
                }
            });

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.category.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            // All ok
            $this->repoCategory->create(
                $request->name, $request->link_title, $request->blurb_option, $request->parent_id,
                $request->taxonomy_reviewed, $request->layout_version, $request->meta_description,
                $request->intro_description,
            );

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FormMode'] = 'add';

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();
        $bindings['BlurbOptionList'] = $this->blurbCategory->getOptions();
        $bindings['LayoutVersionList'] = $this->helperLayoutVersion->buildList();

        return view('staff.categorisation.category.add', $bindings);
    }

    public function editCategory(Request $request, $categoryId)
    {
        $pageTitle = 'Edit category';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationCategoriesSubpage($pageTitle))->bindings;

        $categoryData = $this->repoCategory->find($categoryId);
        if (!$categoryData) abort(404);

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->repoCategory->edit(
                $categoryData, $request->name, $request->link_title, $request->blurb_option, $request->parent_id,
                $request->taxonomy_reviewed, $request->layout_version, $request->meta_description,
                $request->intro_description,
            );

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['CategoryData'] = $categoryData;
        $bindings['CategoryId'] = $categoryId;

        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();
        $bindings['BlurbOptionList'] = $this->blurbCategory->getOptions();
        $bindings['LayoutVersionList'] = $this->helperLayoutVersion->buildList();

        return view('staff.categorisation.category.edit', $bindings);
    }

    public function deleteCategory($categoryId)
    {
        $pageTitle = 'Delete category';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationCategoriesSubpage($pageTitle))->bindings;

        $categoryData = $this->repoCategory->find($categoryId);
        if (!$categoryData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->repoCategory->delete($categoryId);

            // Done

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['CategoryData'] = $categoryData;
        $bindings['CategoryId'] = $categoryId;

        return view('staff.categorisation.category.delete', $bindings);
    }
}