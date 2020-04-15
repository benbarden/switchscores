<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

class CategoryController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'name' => 'required|max:100',
        'link_name' => 'required|max:100',
    ];

    public function showList()
    {
        $serviceNewsCategory = $this->getServiceNewsCategory();

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - News categories';
        $bindings['PageTitle'] = 'News categories';

        $newsCategoryList = $serviceNewsCategory->getAll();

        $bindings['NewsCategoryList'] = $newsCategoryList;

        return view('staff.news.category.list', $bindings);
    }

    public function add()
    {
        $serviceNewsCategory = $this->getServiceNewsCategory();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $serviceNewsCategory->create($request->name, $request->link_name);

            return redirect(route('staff.news.category.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - News - Add news category';
        $bindings['PageTitle'] = 'Add news category';
        $bindings['FormMode'] = 'add';

        return view('staff.news.category.add', $bindings);
    }

    public function edit($newsCategoryId)
    {
        $serviceNewsCategory = $this->getServiceNewsCategory();

        $bindings = [];

        $newsCategory = $serviceNewsCategory->find($newsCategoryId);
        if (!$newsCategory) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceNewsCategory->edit($newsCategory, $request->name, $request->link_name);

            return redirect(route('staff.news.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - News - Edit news';
        $bindings['PageTitle'] = 'Edit news';
        $bindings['NewsCategory'] = $newsCategory;
        $bindings['NewsCategoryId'] = $newsCategoryId;

        return view('staff.news.category.edit', $bindings);
    }
}