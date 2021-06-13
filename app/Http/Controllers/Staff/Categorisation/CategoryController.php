<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

use App\Domain\ViewBreadcrumbs\Staff as Breadcrumbs;

use App\Traits\AuthUser;
use App\Traits\SwitchServices;
use App\Traits\StaffView;

class CategoryController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use StaffView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    protected $viewBreadcrumbs;

    public function __construct(
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function showList()
    {
        $bindings = $this->getBindings('Categories');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationSubpage('Categories');

        $bindings['CategoryList'] = $this->getServiceCategory()->getAll();

        return view('staff.categorisation.category.list', $bindings);
    }

    public function addCategory()
    {
        $bindings = $this->getBindings('Add category');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationCategoriesSubpage('Add category');

        $request = request();

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.category.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingCategory = $this->getServiceCategory()->getByName($request->name);

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
            $this->getServiceCategory()->create($request->name, $request->link_title, $request->blurb_option, $request->parent_id);

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['FormMode'] = 'add';

        $bindings['CategoryList'] = $this->getServiceCategory()->getAllWithoutParents();
        $bindings['BlurbOptionList'] = $this->getServiceCategory()->getBlurbOptions();

        return view('staff.categorisation.category.add', $bindings);
    }

    public function editCategory($categoryId)
    {
        $bindings = $this->getBindings('Edit category');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationCategoriesSubpage('Edit category');

        $categoryData = $this->getServiceCategory()->find($categoryId);
        if (!$categoryData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->getServiceCategory()->edit($categoryData, $request->name, $request->link_title, $request->blurb_option, $request->parent_id);

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['CategoryData'] = $categoryData;
        $bindings['CategoryId'] = $categoryId;

        $bindings['CategoryList'] = $this->getServiceCategory()->getAllWithoutParents();
        $bindings['BlurbOptionList'] = $this->getServiceCategory()->getBlurbOptions();

        return view('staff.categorisation.category.edit', $bindings);
    }

    public function deleteCategory($categoryId)
    {
        $bindings = $this->getBindings('Delete category');
        $bindings['crumbNav'] = $this->viewBreadcrumbs->categorisationCategoriesSubpage('Delete category');

        $categoryData = $this->getServiceCategory()->find($categoryId);
        if (!$categoryData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $this->getServiceCategory()->delete($categoryId);

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