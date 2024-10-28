<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\NewsCategory\Repository as NewsCategoryRepository;

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

    public function __construct(
        private NewsCategoryRepository $repoNewsCategory
    )
    {
    }

    public function showList()
    {
        $pageTitle = 'News categories';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['NewsCategoryList'] = $this->repoNewsCategory->getAll();

        return view('staff.news.category.list', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add news category';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsCategoriesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $this->repoNewsCategory->create($request->name, $request->link_name);

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

        $newsCategory = $this->repoNewsCategory->find($newsCategoryId);
        if (!$newsCategory) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->repoNewsCategory->edit($newsCategory, $request->name, $request->link_name);

            return redirect(route('staff.news.category.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['NewsCategory'] = $newsCategory;
        $bindings['NewsCategoryId'] = $newsCategoryId;

        return view('staff.news.category.edit', $bindings);
    }
}