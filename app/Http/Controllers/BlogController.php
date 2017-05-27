<?php

namespace App\Http\Controllers;

class BlogController extends BaseController
{
    public function redirection($year, $month, $day, $title)
    {
        $url = sprintf('https://worldofswitch.wordpress.com/%s/%s/%s/%s', $year, $month, $day, $title);
        return redirect($url, 301);
    }
}
