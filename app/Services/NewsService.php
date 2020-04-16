<?php


namespace App\Services;

use App\News;
use Carbon\Carbon;


class NewsService
{
    public function create(
        $title, $categoryId, $url, $contentHtml, $gameId, $customImageUrl
    )
    {
        return News::create([
            'title' => $title,
            'category_id' => $categoryId,
            'url' => $url,
            'content_html' => $contentHtml,
            'game_id' => $gameId,
            'custom_image_url' => $customImageUrl,
        ]);
    }

    public function edit(
        News $news, $title, $categoryId, $url, $contentHtml, $gameId, $customImageUrl
    )
    {
        $values = [
            'title' => $title,
            'category_id' => $categoryId,
            'url' => $url,
            'content_html' => $contentHtml,
            'game_id' => $gameId,
            'custom_image_url' => $customImageUrl,
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

    public function getPaginatedByCategory($categoryId, $limit)
    {
        return News::where('category_id', $categoryId)->orderBy('created_at', 'desc')->simplePaginate($limit);
    }

    public function getByCategory($categoryId, $limit)
    {
        return News::where('category_id', $categoryId)->orderBy('created_at', 'desc')->limit($limit)->get();
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
     * @return News
     */
    public function getByUrl($url)
    {
        $news = News::where('url', $url)
            ->first();
        return $news;
    }

    public function getByGameId($gameId, $limit = null)
    {
        $news = News::where('game_id', $gameId)->orderBy('created_at', 'desc');
        if ($limit) {
            $news = $news->limit($limit);
        }
        return $news->get();
    }

    public function getNext(News $news)
    {
        $news = News::where('created_at', '>', $news->created_at)
            ->orderBy('created_at', 'ASC')
            ->first();
        return $news;
    }

    public function getPrevious(News $news)
    {
        $news = News::where('created_at', '<', $news->created_at)
            ->orderBy('created_at', 'DESC')
            ->first();
        return $news;
    }
}