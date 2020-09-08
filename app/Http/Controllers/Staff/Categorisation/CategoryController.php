<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

use Illuminate\Routing\Controller as Controller;

use App\Traits\AuthUser;
use App\Traits\SwitchServices;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    use SwitchServices;
    use AuthUser;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required',
        'link_title' => 'required',
    ];

    public function showList()
    {
        $serviceCategory = $this->getServiceCategory();

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - Categories';
        $bindings['PageTitle'] = 'Categories';

        $bindings['CategoryList'] = $serviceCategory->getAll();

        return view('staff.categorisation.category.list', $bindings);
    }

    public function addCategory()
    {
        $serviceCategory = $this->getServiceCategory();

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            //$this->validate($request, $this->validationRules);

            $validator = Validator::make($request->all(), $this->validationRules);

            if ($validator->fails()) {
                return redirect(route('staff.categorisation.category.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $existingCategory = $serviceCategory->getByName($request->name);

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
            $serviceCategory->create($request->name, $request->link_title, $request->blurb_option, $request->parent_id);

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - Add category';
        $bindings['PageTitle'] = 'Add category';
        $bindings['FormMode'] = 'add';

        $bindings['CategoryList'] = $serviceCategory->getAllWithoutParents();
        $bindings['BlurbOptionList'] = $serviceCategory->getBlurbOptions();

        return view('staff.categorisation.category.add', $bindings);
    }

    public function editCategory($categoryId)
    {
        $serviceCategory = $this->getServiceCategory();

        $categoryData = $serviceCategory->find($categoryId);
        if (!$categoryData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceCategory->edit($categoryData, $request->name, $request->link_title, $request->blurb_option, $request->parent_id);

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - Edit category';
        $bindings['PageTitle'] = 'Edit category';
        $bindings['CategoryData'] = $categoryData;
        $bindings['CategoryId'] = $categoryId;

        $bindings['CategoryList'] = $serviceCategory->getAllWithoutParents();
        $bindings['BlurbOptionList'] = $serviceCategory->getBlurbOptions();

        return view('staff.categorisation.category.edit', $bindings);
    }

    public function deleteCategory($categoryId)
    {
        $serviceCategory = $this->getServiceCategory();

        $categoryData = $serviceCategory->find($categoryId);
        if (!$categoryData) abort(404);

        $request = request();

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'delete-post';

            $serviceCategory->delete($categoryId);

            // Done

            return redirect(route('staff.categorisation.category.list'));

        } else {

            $bindings['FormMode'] = 'delete';

        }

        $bindings['TopTitle'] = 'Staff - Categorisation - Delete category';
        $bindings['PageTitle'] = 'Delete category';
        $bindings['CategoryData'] = $categoryData;
        $bindings['CategoryId'] = $categoryId;

        return view('staff.categorisation.category.delete', $bindings);
    }
}