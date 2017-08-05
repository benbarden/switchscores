<?php


namespace App\Services;

use App\Genre;


class GenreService
{
    /**
     * @return mixed
     */
    public function getAll()
    {
        $genreList = Genre::
            orderBy('genre', 'asc')
            ->get();
        return $genreList;
    }

    public function getByLinkTitle($linkTitle)
    {
        $genre = Genre::
            where('link_title', $linkTitle)
            ->first();
        return $genre;
    }
}