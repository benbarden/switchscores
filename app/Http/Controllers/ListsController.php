<?php

namespace App\Http\Controllers;

class ListsController extends BaseController
{
    public function releasedGames()
    {
        return redirect(route('games.list.released'), 301);
    }

    public function upcomingGames()
    {
        return redirect(route('games.list.upcoming'), 301);
    }
}
