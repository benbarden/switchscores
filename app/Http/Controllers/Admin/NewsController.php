<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\ServiceContainer;

class NewsController extends Controller
{
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

    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - News';
        $bindings['PanelTitle'] = 'News';

        $newsService = $serviceContainer->getNewsService();
        $newsList = $newsService->getAll();

        $bindings['NewsList'] = $newsList;

        return view('admin.news.list', $bindings);
    }

    public function add()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $newsService = $serviceContainer->getNewsService();
        $gameService = $serviceContainer->getGameService();
        $newsCategoryService = $serviceContainer->getNewsCategoryService();

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $news = $newsService->create(
                $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id
            );

            return redirect(route('admin.news.list'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - News - Add news';
        $bindings['PanelTitle'] = 'Add news';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        $bindings['NewsCategoryList'] = $newsCategoryService->getAll();

        return view('admin.news.add', $bindings);
    }

    public function edit($newsId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $newsService = $serviceContainer->getNewsService();
        $gameService = $serviceContainer->getGameService();
        $newsCategoryService = $serviceContainer->getNewsCategoryService();

        $bindings = [];

        $newsData = $newsService->find($newsId);
        if (!$newsData) abort(404);

        $request = request();

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            $newsService->edit(
                $newsData, $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id
            );

            return redirect(route('admin.news.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - News - Edit news';
        $bindings['PanelTitle'] = 'Edit news';
        $bindings['NewsData'] = $newsData;
        $bindings['NewsId'] = $newsId;

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        $bindings['NewsCategoryList'] = $newsCategoryService->getAll();

        return view('admin.news.edit', $bindings);
    }
}