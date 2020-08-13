<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Collection;

use App\Traits\SwitchServices;

class GamesFilterListController extends Controller
{
    use SwitchServices;

    public function gamesWithTag($tagLinkTitle)
    {
        $serviceGameList = $this->getServiceGameFilterList();
        $serviceTag = $this->getServiceTag();

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
        $serviceGameList = $this->getServiceGameFilterList();

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

    public function gamesWithNoCategoryOrTag()
    {
        $serviceGameList = $this->getServiceGameFilterList();

        $bindings = [];

        $pageTitle = 'Games with no category or tag';
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = $serviceGameList->getGamesWithoutCategoriesOrTags();
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'games-with-no-category-or-tag';

        return view('admin.games-filter.list', $bindings);
    }

    public function gameSeriesTitleMatches($linkTitle)
    {
        $serviceGame = $this->getServiceGame();
        $serviceGameSeries = $this->getServiceGameSeries();

        $gameSeries = $serviceGameSeries->getByLinkTitle($linkTitle);
        if (!$gameSeries) abort(404);

        $seriesId = $gameSeries->id;
        $seriesName = $gameSeries->series;

        $bindings = [];

        $pageTitle = 'Games matching series: '.$seriesName;
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = $serviceGame->getSeriesTitleMatch($seriesName);
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'game-series-title-matches';
        $bindings['FilterValue'] = $seriesName;

        return view('admin.games-filter.list', $bindings);
    }

    public function gameTagTitleMatches($linkTitle)
    {
        $serviceGame = $this->getServiceGame();
        $serviceTag = $this->getServiceTag();
        $serviceGameTag = $this->getServiceGameTag();

        $tag = $serviceTag->getByLinkTitle($linkTitle);
        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $bindings = [];

        $pageTitle = 'Games matching tag: '.$tagName;
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = new Collection();
        $gameTagList = $serviceGame->getTagTitleMatch($tagName);

        if ($gameTagList) {

            foreach ($gameTagList as $game) {
                if (!$serviceGameTag->gameHasTag($game->id, $tagId)) {
                    $gameList->push($game);
                }
            }

        }

        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'game-tag-title-matches';
        $bindings['FilterValue'] = $tagName;

        return view('admin.games-filter.list', $bindings);
    }
}