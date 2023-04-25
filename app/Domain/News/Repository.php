<?php


namespace App\Domain\News;

use App\Models\News;

class Repository
{
    public function find($id)
    {
        return News::find($id);
    }

    public function getAll()
    {
        return News::orderBy('created_at', 'desc')->get();
    }
}