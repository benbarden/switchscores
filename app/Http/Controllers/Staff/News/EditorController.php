<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

    public function add()
    {
        $serviceNews = $this->getServiceNews();
        $serviceNewsCategory = $this->getServiceNewsCategory();
        $serviceGame = $this->getServiceGame();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $news = $serviceNews->create(
                $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id, $request->custom_image_url
            );

            return redirect(route('staff.news.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Staff - News - Add news';
        $bindings['PageTitle'] = 'Add news';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();

        $bindings['NewsCategoryList'] = $serviceNewsCategory->getAll();

        return view('staff.news.editor.add', $bindings);
    }

    public function edit($newsId)
    {
        $serviceNews = $this->getServiceNews();
        $serviceNewsCategory = $this->getServiceNewsCategory();
        $serviceGame = $this->getServiceGame();

        $bindings = [];

        $newsData = $serviceNews->find($newsId);
        if (!$newsData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $serviceNews->edit(
                $newsData, $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id, $request->custom_image_url
            );

            return redirect(route('staff.news.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Staff - News - Edit news';
        $bindings['PageTitle'] = 'Edit news';
        $bindings['NewsData'] = $newsData;
        $bindings['NewsId'] = $newsId;

        $bindings['GamesList'] = $serviceGame->getAll();

        $bindings['NewsCategoryList'] = $serviceNewsCategory->getAll();

        return view('staff.news.editor.edit', $bindings);
    }
}