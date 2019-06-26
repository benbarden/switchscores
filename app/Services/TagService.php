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

    public function edit(Tag $tagData, $tagName, $linkTitle, $primaryTypeId)
    {
        $values = [
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'primary_type_id' => $primaryTypeId
        ];

        $tagData->fill($values);
        $tagData->save();
    }

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