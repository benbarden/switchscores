<?php

namespace App\Http\Controllers;

class BlogController extends BaseController
{
    public function redirectTag($tag)
    {
        abort(404);
        //$url = sprintf('https://worldofswitch.wordpress.com/tag/%s/', $tag);
        //return redirect($url, 301);
    }

    public function redirectCategory($category)
    {
        abort(404);
        //$url = sprintf('https://worldofswitch.wordpress.com/category/%s/', $category);
        //return redirect($url, 301);
    }

    public function redirectPost($year, $month, $day, $title)
    {
        abort(404);
        /*
        if ($year == 'admin') {
            abort(500);
        }
        $url = sprintf('https://worldofswitch.wordpress.com/%s/%s/%s/%s', $year, $month, $day, $title);
        return redirect($url, 301);
        */
    }
}
