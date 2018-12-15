<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class TagsController extends Controller
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $tagService = $serviceContainer->getTagService();

        $bindings = [];

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
