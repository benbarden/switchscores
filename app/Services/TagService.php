<?php


namespace App\Services;

use App\Models\Tag;


class TagService
{
    public function deleteTag($tagId)
    {
        Tag::where('id', $tagId)->delete();
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