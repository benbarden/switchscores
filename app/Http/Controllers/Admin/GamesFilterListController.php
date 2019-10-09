<?php

namespace App\Http\Controllers\Admin;

use App\Traits\WosServices;
use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;
use Illuminate\Support\Collection;

class GamesFilterListController extends Controller
{
    use WosServices;

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

    public function gamesWithGenre($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameList = $serviceContainer->getGameFilterListService();
        $serviceGenre = $serviceContainer->getGenreService();

        $genre = $serviceGenre->getByLinkTitle($linkTitle);
        if (!$genre) abort(404);

        $genreId = $genre->id;
        $genreName = $genre->genre;

        $bindings = [];

        $pageTitle = 'Games with genre: '.$genreName;
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameList = $serviceGameList->getGamesByGenre($genreId);
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'games-with-genre';
        $bindings['FilterValue'] = $genreName;

        return view('admin.games-filter.list', $bindings);
    }

    public function gameSeriesTitleMatches($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameList = $serviceContainer->getGameFilterListService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceGameSeries = $serviceContainer->getGameSeriesService();

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
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGameList = $serviceContainer->getGameFilterListService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceTag = $serviceContainer->getTagService();
        $serviceGameTag = $serviceContainer->getGameTagService();

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

    public function gamesWithGenresNoPrimaryType()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceGameList = $serviceContainer->getGameFilterListService();
        $serviceGameGenre = $serviceContainer->getGameGenreService();

        $bindings = [];

        $pageTitle = 'Games with genres, no primary type';
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $gameListTemp = $serviceGameGenre->getGamesWithGenresNoPrimaryType($regionCode);
        if ($gameListTemp) {
            $gameList = new Collection();
            foreach ($gameListTemp as $gameTemp) {
                $game = $serviceGame->find($gameTemp->id);
                $gameList->push($game);
            }
            $bindings['GameList'] = $gameList;
        }

        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        $bindings['FilterName'] = 'games-with-genres-no-primary-type';

        return view('admin.games-filter.list', $bindings);
    }

    public function gamesWithNoEshopEuropeLink()
    {
        $gameList = $this->getServiceGameFilterList()->getGamesWithoutEshopEuropeFsId();

        $bindings = [];

        $pageTitle = 'Games with no eShop Europe link';
        $bindings['TopTitle'] = 'Admin - '.$pageTitle;
        $bindings['PageTitle'] = $pageTitle;
        $bindings['GameList'] = $gameList;
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        $bindings['FilterName'] = 'games-with-no-eshop-europe-link';

        return view('admin.games-filter.list', $bindings);
    }

}