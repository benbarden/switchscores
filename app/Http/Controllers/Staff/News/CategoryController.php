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

    protected $viewBreadcrumbs;

    public function __construct(
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'News categories';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['NewsCategoryList'] = $this->getServiceNewsCategory()->getAll();

        return view('staff.news.category.list', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add news category';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsCategoriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceNewsCategory = $this->getServiceNewsCategory();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $serviceNewsCategory->create($request->name, $request->link_name);

            return redirect(route('staff.news.category.list'));

        }

        $bindings['FormMode'] = 'add';

        return view('staff.news.category.add', $bindings);
    }

    public function edit($newsCategoryId)
    {
        $pageTitle = 'Edit news category';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsCategoriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $serviceNewsCategory = $this->getServiceNewsCategory();

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

        $bindings['NewsCategory'] = $newsCategory;
        $bindings['NewsCategoryId'] = $newsCategoryId;

        return view('staff.news.category.edit', $bindings);
    }
}