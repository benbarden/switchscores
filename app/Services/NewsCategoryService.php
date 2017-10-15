<?php


namespace App\Services;

use App\NewsCategory;
use Carbon\Carbon;


class NewsCategoryService
{
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