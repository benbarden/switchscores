<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class GenreController extends Controller
{
    public function showList()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Genres';
        $bindings['PageTitle'] = 'Genres';

        $serviceGenre = $serviceContainer->getGenreService();
        $bindings['GenreList'] = $serviceGenre->getAll();

        return view('admin.genre.list', $bindings);
    }
}