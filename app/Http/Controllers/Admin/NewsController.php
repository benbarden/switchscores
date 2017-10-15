<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

class NewsController extends \App\Http\Controllers\BaseController
{
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
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - News';
        $bindings['PanelTitle'] = 'News';

        $newsService = resolve('Services\NewsService');
        $newsList = $newsService->getAll();

        $bindings['NewsList'] = $newsList;

        return view('admin.news.list', $bindings);
    }

    public function add()
    {
        $newsService = resolve('Services\NewsService');
        $gameService = resolve('Services\GameService');
        $newsCategoryService = resolve('Services\NewsCategoryService');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $news = $newsService->create(
                $request->title, $request->category_id, $request->url,
                $request->content_html, $request->game_id
            );

            return redirect(route('admin.news.list'));

        }

        $bindings = array();

        $bindings['TopTitle'] = 'Admin - News - Add news';
        $bindings['PanelTitle'] = 'Add news';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $gameService->getAll();

        $bindings['NewsCategoryList'] = $newsCategoryService->getAll();

        return view('admin.news.add', $bindings);
    }

    public function edit($newsId)
    {
        $newsService = resolve('Services\NewsService');
        $gameService = resolve('Services\GameService');
        $newsCategoryService = resolve('Services\NewsCategoryService');

        $bindings = array();

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

        $bindings['GamesList'] = $gameService->getAll();

        $bindings['NewsCategoryList'] = $newsCategoryService->getAll();

        return view('admin.news.edit', $bindings);
    }
}