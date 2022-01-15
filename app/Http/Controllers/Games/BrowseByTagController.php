<?php

namespace App\Http\Controllers\Games;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Traits\SwitchServices;

class BrowseByTagController extends Controller
{
    use SwitchServices;

    protected $repoGameLists;
    protected $dbGameLists;
    protected $repoTag;
    protected $repoTagCategory;
    protected $viewBreadcrumbs;

    public function __construct(
        GameListsRepository $repoGameLists,
        GameListsDbQueries $dbGameLists,
        TagRepository $repoTag,
        TagCategoryRepository $repoTagCategory,
        Breadcrumbs $viewBreadcrumbs
    )
    {
        $this->repoGameLists = $repoGameLists;
        $this->dbGameLists = $dbGameLists;
        $this->repoTag = $repoTag;
        $this->repoTagCategory = $repoTagCategory;
        $this->viewBreadcrumbs = $viewBreadcrumbs;
    }

    public function landing()
    {
        $bindings = [];

        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by tag';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by tag';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By tag');

        return view('games.browse.byTagLanding', $bindings);
    }

    public function page($tag)
    {
        $bindings = [];

        $tag = $this->getServiceTag()->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $gameList = $this->dbGameLists->getByTagWithDates($tagId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by tag: '.$tagName;
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by tag: '.$tagName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByTagSubpage($tagName);

        return view('games.browse.byTagPage', $bindings);
    }
}
