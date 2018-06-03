<?php


namespace App\Services;

use App\CrawlerWikipediaGamesListSource;


class CrawlerWikipediaGamesListSourceService
{
    public function find($id)
    {
        return CrawlerWikipediaGamesListSource::find($id);
    }

    public function getAll()
    {
        $list = CrawlerWikipediaGamesListSource::
            orderBy('id', 'asc')
            ->get();
        return $list;
    }
}