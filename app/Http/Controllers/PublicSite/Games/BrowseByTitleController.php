<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use Illuminate\Routing\Controller as Controller;

class BrowseByTitleController extends Controller
{
    public function __construct(
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Browse Nintendo Switch games by title';
        $bindings['PageTitle'] = 'Browse Nintendo Switch games by title';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By title');

        $bindings['LetterList'] = range('A', 'Z');

        return view('public.games.browse.byTitleLanding', $bindings);
    }

    public function page($letter)
    {
        return redirect(route('games.browse.byTitle.landing'));
    }
}
