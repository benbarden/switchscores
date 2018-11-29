<?php


namespace App\Services;

use App\Tag;


class TagService
{
    public function create($tagName, $linkTitle)
    {
        Tag::create([
            'tag_name' => $tagName,
            'link_title' => $linkTitle
        ]);
    }

    public function find($tagId)
    {
        return Tag::find($tagId);
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

    public function getByLinkTitle($linkTitle)
    {
        $tag = Tag::
            where('link_title', $linkTitle)
            ->first();
        return $tag;
    }
}