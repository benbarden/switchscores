<?php

namespace App\Http\Controllers\PublicSite\Console;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\PublicBreadcrumbs;
use App\Domain\View\PageBuilders\PublicPageBuilder;

use App\Domain\Tag\Repository as TagRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

use App\Models\Console;

class BrowseByTagController extends Controller
{
    public function __construct(
        private PublicPageBuilder $pageBuilder,
        private TagRepository $repoTag,
        private TagCategoryRepository $repoTagCategory,
    )
    {
    }

    public function landing(Console $console)
    {
        $pageTitle = 'Nintendo '.$console->name.' games list - By tag';
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleSubpage('By tag', $console))->bindings;

        $bindings['TagCategoryList'] = $this->repoTagCategory->getAll();

        $bindings['Console'] = $console;

        return view('public.console.by-tag.landing', $bindings);
    }

    public function page(Console $console, $tag)
    {
        $tag = $this->repoTag->getByLinkTitle($tag);

        if (!$tag) abort(404);

        $consoleId = $console->id;
        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $pageTitle = 'Nintendo '.$console->name.' games list - By tag: '.$tagName;
        $bindings = $this->pageBuilder->build($pageTitle, PublicBreadcrumbs::consoleTagSubpage($tagName, $console))->bindings;

        // Lists
        $bindings['RankedGameList'] = $this->repoTag->rankedByTag($consoleId, $tagId);
        $bindings['UnrankedGameList'] = $this->repoTag->unrankedByTag($consoleId, $tagId);
        $bindings['DelistedGameList'] = $this->repoTag->delistedByTag($consoleId, $tagId);
        $bindings['LowQualityGameList'] = $this->repoTag->lowQualityByTag($consoleId, $tagId);

        $bindings['Console'] = $console;

        return view('public.console.by-tag.page', $bindings);
    }
}
