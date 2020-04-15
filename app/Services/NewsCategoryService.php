<?php


namespace App\Services;

use App\NewsCategory;
use Carbon\Carbon;

class NewsCategoryService
{
    public function create(
        $name, $linkName
    )
    {
        return NewsCategory::create([
            'name' => $name,
            'link_name' => $linkName,
        ]);
    }

    public function edit(
        NewsCategory $newsCategory, $name, $linkName
    )
    {
        $values = [
            'name' => $name,
            'link_name' => $linkName,
        ];

        $newsCategory->fill($values);
        $newsCategory->save();
    }

    public function find($id)
    {
        return NewsCategory::find($id);
    }

    public function getAll()
    {
        return NewsCategory::orderBy('name', 'asc')->get();
    }

    /**
     * Gets a news category from its URL
     * @param string $url
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getByUrl($url)
    {
        $newsCategory = NewsCategory::where('link_name', $url)
            ->first();
        return $newsCategory;
    }
}