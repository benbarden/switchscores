<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;
use App\Domain\GameLists\Repository as GameListsRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;
use App\Traits\SwitchServices;
use Illuminate\Routing\Controller as Controller;

class BrowseByTagController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameListsRepository $repoGameLists,
        private GameListsDbQueries $dbGameLists,
        private TagRepository $repoTag,
        private TagCategoryRepository $repoTagCategory,
        private Breadcrumbs $viewBreadcrumbs
    )
    {
    }

    public function landing()
    {
        $bindings = [];

        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        $bindings['PageTitle'] = 'Browse Nintendo Switch games by tag';
        $bindings['TopTitle'] = 'Browse Nintendo Switch games by tag';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By tag');

        return view('public.games.browse.byTagLanding', $bindings);
    }

    public function page($tag)
    {
        $bindings = [];

        $tag = $this->repoTag->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        // Lists
        $bindings['RankedGameList'] = $this->repoTag->rankedByTag($tagId);
        $bindings['UnrankedGameList'] = $this->repoTag->unrankedByTag($tagId);
        $bindings['DelistedGameList'] = $this->repoTag->delistedByTag($tagId);

        $bindings['PageTitle'] = 'Browse games by tag: '.$tagName;
        $bindings['TopTitle'] = 'Browse games by tag: '.$tagName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByTagSubpage($tagName);

        return view('public.games.browse.tag.page-landing', $bindings);
    }
}
