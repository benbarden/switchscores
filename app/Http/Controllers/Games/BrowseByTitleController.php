<?php

namespace App\Http\Controllers\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

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

        return view('games.browse.byTitleLanding', $bindings);
    }

    public function page($letter)
    {
        $bindings = [];

        $gamesList = $this->getServiceGameReleaseDate()->getReleasedByLetter($letter);

        $bindings['GameList'] = $gamesList;
        $bindings['GameLetter'] = $letter;

        $bindings['TopTitle'] = 'Browse Nintendo Switch games by title: '.$letter;
        $bindings['PageTitle'] = 'Browse Nintendo Switch games by title: '.$letter;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByTitleSubpage($letter);

        return view('games.browse.byTitlePage', $bindings);
    }
}
