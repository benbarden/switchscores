<?php


namespace App\Domain\Category;

use App\Models\Category;

class Repository
{
    public function find($id)
    {
        return Category::find($id);
    }

    public function getByLinkTitle($linkTitle)
    {
        return Category::where('link_title', $linkTitle)->first();
    }

    public function topLevelCategories()
    {
        return Category::whereDoesntHave('parent')->orderBy('name', 'asc')->get();
    }
}