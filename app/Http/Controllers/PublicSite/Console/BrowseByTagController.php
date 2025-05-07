<?php

namespace App\Http\Controllers\PublicSite\Console;

use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;
use App\Domain\ViewBreadcrumbs\MainSite as Breadcrumbs;

use App\Models\Console;

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

    public function landing(Console $console)
    {
        $bindings = [];

        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        $bindings['Console'] = $console;

        $bindings['PageTitle'] = 'Nintendo '.$console->name.' games list - By tag';
        $bindings['TopTitle'] = 'Nintendo '.$console->name.' games list - By tag';
        $bindings['crumbNav'] = $this->viewBreadcrumbs->consoleSubpage('By tag', $console);

        return view('public.console.by-tag.landing', $bindings);
    }

    public function page(Console $console, $tag)
    {
        $bindings = [];

        $tag = $this->repoTag->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $consoleId = $console->id;
        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        // Lists
        $bindings['RankedGameList'] = $this->repoTag->rankedByTag($consoleId, $tagId);
        $bindings['UnrankedGameList'] = $this->repoTag->unrankedByTag($consoleId, $tagId);
        $bindings['DelistedGameList'] = $this->repoTag->delistedByTag($consoleId, $tagId);
        $bindings['LowQualityGameList'] = $this->repoTag->lowQualityByTag($consoleId, $tagId);

        $bindings['Console'] = $console;

        $bindings['PageTitle'] = 'Nintendo '.$console->name.' games list - By tag: '.$tagName;
        $bindings['TopTitle'] = 'Nintendo '.$console->name.' games list - By tag: '.$tagName;
        $bindings['crumbNav'] = $this->viewBreadcrumbs->consoleTagSubpage($tagName, $console);

        return view('public.console.by-tag.page', $bindings);
    }
}
