<?php

namespace App\Http\Controllers\PublicSite\Games;

use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use Illuminate\Routing\Controller as Controller;

class BrowseByTagController extends Controller
{
    public function __construct(
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

        $bindings['PageTitle'] = 'Nintendo Switch games list - By tag';
        $bindings['TopTitle'] = 'Nintendo Switch games list - By tag';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesSubpage('By tag');

        return view('public.games.browse.tag.landing', $bindings);
    }

    public function page($tag)
    {
        $bindings = [];

        $tag = $this->repoTag->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        // Lists
        $bindings['RankedGameList'] = $this->repoTag->rankedByTag(1, $tagId);
        $bindings['UnrankedGameList'] = $this->repoTag->unrankedByTag(1, $tagId);
        $bindings['DelistedGameList'] = $this->repoTag->delistedByTag(1, $tagId);

        $bindings['PageTitle'] = 'Nintendo Switch games list - By tag: '.$tagName;
        $bindings['TopTitle'] = 'Nintendo Switch games list - By tag: '.$tagName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->gamesByTagSubpage($tagName);

        return view('public.games.browse.tag.page', $bindings);
    }
}
