<?php


namespace App\Domain\News;

use App\Models\News;

class Repository
{
    public function find($id)
    {
        return News::find($id);
    }
}