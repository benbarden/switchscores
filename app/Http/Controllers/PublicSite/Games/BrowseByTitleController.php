<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class BrowseByTitleController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $dbGameLists;
    protected $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        GameListsDbQueries $dbGameLists,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->dbGameLists = $dbGameLists;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
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
