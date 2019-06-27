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

    public function gamesWithNoTag()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameList = $serviceContainer->getGameFilterListService();

        $bindings = [];

        $pageTitle = 'Games with no tag';
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = $serviceGameList->getGamesWithoutTags();
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'games-with-no-tag';

        return view('admin.games-filter.list', $bindings);
    }

    public function gamesWithNoTypeOrTag()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameList = $serviceContainer->getGameFilterListService();

        $bindings = [];

        $pageTitle = 'Games with no type or tag';
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = $serviceGameList->getGamesWithoutTypesOrTags();
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'games-with-no-type-or-tag';

        return view('admin.games-filter.list', $bindings);
    }
}