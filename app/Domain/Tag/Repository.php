<?php


namespace App\Domain\Tag;

use App\Models\Tag;

class Repository
{
    public function create($tagName, $linkTitle, $tagCategoryId)
    {
        Tag::create([
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'tag_category_id' => $tagCategoryId,
        ]);
    }

    public function edit(Tag $tagData, $tagName, $linkTitle, $tagCategoryId)
    {
        $values = [
            'tag_name' => $tagName,
            'link_title' => $linkTitle,
            'tag_category_id' => $tagCategoryId,
        ];

        $tagData->fill($values);
        $tagData->save();
    }

    public function find($tagId)
    {
        return Tag::find($tagId);
    }

    public function getAll()
    {
        return Tag::orderBy('tag_name', 'asc')->get();
    }
}