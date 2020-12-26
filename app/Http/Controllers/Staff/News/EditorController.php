<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class EditorController extends Controller
{
    use SwitchServices;
    use StaffView;

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

    public function add()
    {
        $bindings = $this->getBindingsNewsListSubpage('Add news');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $news = $this->getServiceNews()->create(
                $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id, $request->custom_image_url
            );

            return redirect(route('staff.news.list'));

        }

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $this->getServiceGame()->getAll();

        $bindings['NewsCategoryList'] = $this->getServiceNewsCategory()->getAll();

        return view('staff.news.editor.add', $bindings);
    }

    public function edit($newsId)
    {
        $bindings = $this->getBindingsNewsListSubpage('Edit news');

        $newsData = $this->getServiceNews()->find($newsId);
        if (!$newsData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $this->getServiceNews()->edit(
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

        $bindings['NewsCategoryList'] = $this->getServiceNewsCategory()->getAll();

        return view('staff.news.editor.edit', $bindings);
    }
}