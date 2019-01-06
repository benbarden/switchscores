<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

class PublishersController extends Controller
{
    public function page($linkTitle)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $servicePublisher = $serviceContainer->getPublisherService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $bindings = [];

        $publisher = $servicePublisher->getByLinkTitle($linkTitle);

        if (!$publisher) abort(404);

        $publisherId = $publisher->id;
        $publisherName = $publisher->name;

        $gameList = $serviceGamePublisher->getGamesByPublisher($regionCode, $publisherId);

        $bindings['PublisherData'] = $publisher;
        $bindings['GameList'] = $gameList;

        $bindings['PageTitle'] = $publisherName.' - Nintendo Switch games publisher';
        $bindings['TopTitle'] = $publisherName.' - Nintendo Switch games publisher';

        return view('publishers.page', $bindings);
    }
}
