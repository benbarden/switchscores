<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class GenreController extends Controller
{
    use SwitchServices;

    public function showList()
    {
        $serviceGenre = $this->getServiceGenre();

        $bindings = [];

        $bindings['TopTitle'] = 'Admin - Genres';
        $bindings['PageTitle'] = 'Genres';

        $bindings['GenreList'] = $serviceGenre->getAll();

        return view('staff.categorisation.genre.list', $bindings);
    }
}