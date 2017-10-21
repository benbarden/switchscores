<?php


namespace App\Services;

use App\News;
use Carbon\Carbon;


class NewsService
{
    public function create(
        $title, $categoryId, $url, $contentHtml, $gameId
    )
    {
        return News::create([
            'title' => $title,
            'category_id' => $categoryId,
            'url' => $url,
            'content_html' => $contentHtml,
            'game_id' => $gameId,
        ]);
    }

    public function edit(
        News $news, $title, $categoryId, $url, $contentHtml, $gameId
    )
    {
        $values = [
            'title' => $title,
            'category_id' => $categoryId,
            'url' => $url,
            'content_html' => $contentHtml,
            'game_id' => $gameId,
        ];

        $news->fill($values);
        $news->save();
    }

    // ********************************************************** //

    public function find($id)
    {
        return News::find($id);
    }

    public function getAll()
    {
        return News::orderBy('created_at', 'desc')->get();
    }

    public function getPaginated($limit)
    {
        return News::orderBy('created_at', 'desc')->simplePaginate($limit);
    }

    /**
     * @return mixed
     */
    public function getAllWithLimit($limit)
    {
        $newsList = News::
              orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
        return $newsList;
    }

    /**
     * Gets a news item from its URL
     * @param string $url
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getByUrl($url)
    {
        $news = News::where('url', $url)
            ->first();
        return $news;
    }
}