<?php


namespace App\Services;

use App\Category;


class CategoryService
{
    public function create($name, $linkTitle, $parentId = null)
    {
        Category::create([
            'name' => $name,
            'link_title' => $linkTitle,
            'parent_id' => $parentId,
        ]);
    }

    public function find($id)
    {
        return Category::find($id);
    }

    public function delete($id)
    {
        Category::where('id', $id)->delete();
    }

    /**
     * @return mixed
     */
    public function getAll()
    {
        return Category::orderBy('name', 'asc')->get();
    }

    public function getByName($name)
    {
        return Category::where('name', $name)->first();
    }

    public function getByLinkTitle($linkTitle)
    {
        return Category::where('link_title', $linkTitle)->first();
    }
}