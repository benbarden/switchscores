<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class BlogController extends Controller
{
    public function redirectTag($tag)
    {
        abort(404);
    }

    public function redirectCategory($category)
    {
        abort(404);
    }

    public function redirectPost($year, $month, $day, $title)
    {
        abort(404);
    }
}
