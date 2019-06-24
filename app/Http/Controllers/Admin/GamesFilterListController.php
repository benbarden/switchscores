<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class GamesFilterListController extends Controller
{
    public function gamesWithTag($tagLinkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameList = $serviceContainer->getGameFilterListService();
        $serviceTag = $serviceContainer->getTagService();

        $tag = $serviceTag->getByLinkTitle($tagLinkTitle);
        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $bindings = [];

        $pageTitle = 'Games with tag: '.$tagName;
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = $serviceGameList->getByTag($tagId);
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        $bindings['FilterName'] = 'games-with-tag';

        return view('admin.games-filter.list', $bindings);
    }
}