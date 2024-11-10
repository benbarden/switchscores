<?php

namespace App\Http\Controllers\PublicSite\Games;

use Illuminate\Routing\Controller as Controller;

class BrowseByTitleController extends Controller
{
    public function __construct(
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Browse Nintendo Switch games by title';
        $breadcrumbs = resolve('View/Breadcrumbs/MainSite')->gamesSubpage('By title');
        $bindings = resolve('View/Bindings/MainSite')->setBreadcrumbs($breadcrumbs)->generateMain($pageTitle);

        return view('public.games.browse.byTitleLanding', $bindings);
    }

    public function page($letter)
    {
        return redirect(route('games.browse.byTitle.landing'));
    }
}
