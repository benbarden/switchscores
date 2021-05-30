<?php


namespace App\Domain\Category;

use App\Category;


class Repository
{
    public function find($id)
    {
        return Category::find($id);
    }
}