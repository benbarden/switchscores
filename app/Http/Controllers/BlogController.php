<?php

namespace App\Http\Controllers;

class BlogController extends BaseController
{
    public function redirectTag($tag)
    {
        $url = sprintf('https://worldofswitch.wordpress.com/tag/%s/', $tag);
        return redirect($url, 301);
    }

    public function redirectCategory($category)
    {
        $url = sprintf('https://worldofswitch.wordpress.com/category/%s/', $category);
        return redirect($url, 301);
    }

    public function redirectPost($year, $month, $day, $title)
    {
        if ($year == 'admin') {
            abort(500);
        }
        $url = sprintf('https://worldofswitch.wordpress.com/%s/%s/%s/%s', $year, $month, $day, $title);
        return redirect($url, 301);
    }
}
