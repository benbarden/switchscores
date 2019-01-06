<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class DevelopersController extends Controller
{
    public function page($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceDeveloper = $serviceContainer->getDeveloperService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $bindings = [];

        $developer = $serviceDeveloper->getByLinkTitle($linkTitle);

        if (!$developer) abort(404);

        $developerId = $developer->id;
        $developerName = $developer->name;

        $gameList = $serviceGameDeveloper->getGamesByDeveloper($regionCode, $developerId);

        $bindings['DeveloperData'] = $developer;
        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = $developerName.' - Nintendo Switch games developer';
        $bindings['TopTitle'] = $developerName.' - Nintendo Switch games developer';

        return view('developers.page', $bindings);
    }
}
