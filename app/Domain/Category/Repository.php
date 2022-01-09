<?php


namespace App\Domain\Category;

use App\Models\Category;


class Repository
{
    public function find($id)
    {
        return Category::find($id);
    }
}