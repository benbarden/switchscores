<?php


namespace App\Domain\TagCategory;

use App\Models\TagCategory;

class Repository
{
    public function getAll()
    {
        return TagCategory::orderBy('category_order', 'asc')->get();
    }
}