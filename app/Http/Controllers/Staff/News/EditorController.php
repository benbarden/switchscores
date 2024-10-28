<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Domain\News\Repository as NewsRepository;
use App\Domain\NewsCategory\Repository as NewsCategoryRepository;

use App\Traits\SwitchServices;

class EditorController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'title' => 'required|max:100',
        'category_id' => 'required|exists:news_categories,id',
        'url' => 'required|max:150',
        'content_html' => 'required',
        //'game_id' => 'exists:games,id',
    ];

    public function __construct(
        private NewsRepository $repoNews,
        private NewsCategoryRepository $repoNewsCategory
    )
    {
    }

    public function add()
    {
        $pageTitle = 'Add news';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsListSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $news = $this->repoNews->create(
                $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id, $request->custom_image_url
            );

            return redirect(route('staff.news.list'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['NewsCategoryList'] = $this->repoNewsCategory->getAll();

        return view('staff.news.editor.add', $bindings);
    }

    public function edit($newsId)
    {
        $pageTitle = 'Edit news';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsListSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $newsData = $this->repoNews->find($newsId);
        if (!$newsData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->repoNews->edit(
                $newsData, $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id, $request->custom_image_url
            );

            return redirect(route('staff.news.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['NewsData'] = $newsData;
        $bindings['NewsId'] = $newsId;

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['NewsCategoryList'] = $this->repoNewsCategory->getAll();

        return view('staff.news.editor.edit', $bindings);
    }
}