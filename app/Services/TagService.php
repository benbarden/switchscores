<?php


namespace App\Services;

use App\Tag;


class TagService
{
    public function create($tagName)
    {
        Tag::create([
            'tag_name' => $tagName
        ]);
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        $tagList = Tag::
            orderBy('tag_name', 'asc')
            ->get();
        return $tagList;
    }

    public function getByName($name)
    {
        $tag = Tag::
            where('tag_name', $name)
            ->first();
        return $tag;
    }
}