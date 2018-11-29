<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class TagsController extends Controller
{
    public function getListData($listName)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();

        $gamesList = null;

        switch ($listName) {
            case 'aca-neogeo':
                $gamesList = $serviceGame->getListAcaNeoGeo($regionCode);
                break;
            case 'arcade-archives':
                $gamesList = $serviceGame->getListArcadeArchives($regionCode);
                break;
            case 'golf-games':
                $gamesList = $serviceGame->getListGolfGames($regionCode);
                break;
            case 'lego-games':
                $gamesList = $serviceGame->getListLegoGames($regionCode);
                break;
            case 'mahjong-games':
                $gamesList = $serviceGame->getListMahjongGames($regionCode);
                break;
            case 'pinball-games':
                $gamesList = $serviceGame->getListPinballGames($regionCode);
                break;
            case 'developer-nintendo-epd':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'Nintendo EPD');
                break;
            case 'developer-arc-system-works':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'Arc System Works');
                break;
            case 'developer-10tons':
                $gamesList = $serviceGame->getByDeveloper($regionCode, '10tons');
                break;
            case 'developer-capcom':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'Capcom');
                break;
            case 'developer-sometimes-you':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'Sometimes You');
                break;
            case 'developer-forever-entertainment':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'Forever Entertainment');
                break;
            case 'developer-uig-entertainment':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'UIG Entertainment');
                break;
            case 'developer-enjoyup-games':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'EnjoyUp Games');
                break;
            case 'developer-inti-creates':
                $gamesList = $serviceGame->getByDeveloper($regionCode, 'Inti Creates');
                break;
        }

        return $gamesList;
    }

    public function getPageTitle($listName)
    {
        $pageTitle = 'A list of games';

        switch ($listName) {
            case 'aca-neogeo':
                $pageTitle = 'ACA NeoGeo games';
                break;
            case 'arcade-archives':
                $pageTitle = 'Arcade Archives games';
                break;
            case 'golf-games':
                $pageTitle = 'Golf games';
                break;
            case 'lego-games':
                $pageTitle = 'Lego games';
                break;
            case 'mahjong-games':
                $pageTitle = 'Mahjong games';
                break;
            case 'pinball-games':
                $pageTitle = 'Pinball games';
                break;
            case 'developer-nintendo-epd':
                $pageTitle = 'Games developed by Nintendo EPD';
                break;
            case 'developer-arc-system-works':
                $pageTitle = 'Games developed by Arc System Works';
                break;
            case 'developer-10tons':
                $pageTitle = 'Games developed by 10tons';
                break;
            case 'developer-capcom':
                $pageTitle = 'Games developed by Capcom';
                break;
            case 'developer-sometimes-you':
                $pageTitle = 'Games developed by Sometimes You';
                break;
            case 'developer-forever-entertainment':
                $pageTitle = 'Games developed by Forever Entertainment';
                break;
            case 'developer-uig-entertainment':
                $pageTitle = 'Games developed by UIG Entertainment';
                break;
            case 'developer-enjoyup-games':
                $pageTitle = 'Games developed by EnjoyUp Games';
                break;
            case 'developer-inti-creates':
                $pageTitle = 'Games developed by Inti Creates';
                break;
        }

        return $pageTitle;
    }

    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $tagService = $serviceContainer->getTagService();

        $bindings = [];

        /*
        $listUrls = [
            ['listName' => 'aca-neogeo', 'text' => 'ACA NeoGeo'],
            ['listName' => 'arcade-archives', 'text' => 'Arcade Archives'],
            ['listName' => 'golf-games', 'text' => 'Golf games'],
            ['listName' => 'lego-games', 'text' => 'Lego games'],
            ['listName' => 'mahjong-games', 'text' => 'Mahjong games'],
            ['listName' => 'pinball-games', 'text' => 'Pinball games'],
            ['listName' => 'developer-nintendo-epd', 'text' => 'Games developed by Nintendo EPD'],
            ['listName' => 'developer-10tons', 'text' => 'Games developed by 10tons'],
            ['listName' => 'developer-arc-system-works', 'text' => 'Games developed by Arc System Works'],
            ['listName' => 'developer-capcom', 'text' => 'Games developed by Capcom'],
            ['listName' => 'developer-enjoyup-games', 'text' => 'Games developed by EnjoyUp Games'],
            ['listName' => 'developer-forever-entertainment', 'text' => 'Games developed by Forever Entertainment'],
            ['listName' => 'developer-inti-creates', 'text' => 'Games developed by Inti Creates'],
            ['listName' => 'developer-sometimes-you', 'text' => 'Games developed by Sometimes You'],
            ['listName' => 'developer-uig-entertainment', 'text' => 'Games developed by UIG Entertainment'],
        ];

        foreach ($listUrls as &$listUrl) {
            $listName = $listUrl['listName'];
            $listGameIds = $this->getListData($listName);
            $listUrl['count'] = count($listGameIds);
            $listUrl['url'] = route('tags.page', ['listName' => $listName]);
        }

        $bindings['ListUrls'] = $listUrls;
        */

        $bindings['TagList'] = $tagService->getAll();

        $bindings['PageTitle'] = 'Tags';
        $bindings['TopTitle'] = 'Tags - Nintendo Switch games';

        return view('tags.landing', $bindings);
    }

    public function page($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $tagService = $serviceContainer->getTagService();
        $gameTagService = $serviceContainer->getGameTagService();

        $bindings = [];

        $tag = $tagService->getByLinkTitle($linkTitle);

        if (!$tag) abort(404);

        $tagId = $tag->id;
        $tagName = $tag->tag_name;

        $gameList = $gameTagService->getGamesByTag($regionCode, $tagId);

        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = $tagName.' - Nintendo Switch games by tag';
        $bindings['TopTitle'] = $tagName.' - Nintendo Switch games by tag';

        return view('tags.page', $bindings);
    }
}
