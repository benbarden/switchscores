<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;

class GamesDetailController extends Controller
{
    public function show($gameId)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceReviewLink = $serviceContainer->getReviewLinkService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceGameGenres = $serviceContainer->getGameGenreService();
        $serviceGameTag = $serviceContainer->getGameTagService();
        $serviceReviewUser = $serviceContainer->getReviewUserService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();
        $serviceGameChangeHistory = $serviceContainer->getGameChangeHistoryService();

        $game = $serviceGame->find($gameId);
        if (!$game) abort(404);

        $gameTitle = $game->title;

        $bindings = [];

        $bindings['TopTitle'] = $gameTitle.' - Game detail - Admin';
        $bindings['PageTitle'] = $gameTitle;

        $bindings['LastAction'] = $lastAction = \Request::get('lastaction');

        $lastGameId = \Request::get('lastgameid');
        if ($lastGameId) {
            $lastGame = $serviceGame->find($lastGameId);
            if ($lastGame) {
                $bindings['LastGame'] = $lastGame;
            }
        }

        // Get all the data
        $gameReviews = $serviceReviewLink->getByGame($gameId);
        $gameGenres = $serviceGameGenres->getByGame($gameId);
        $gameUserReviews = $serviceReviewUser->getActiveByGame($gameId);
        $gameDevelopers = $serviceGameDeveloper->getByGame($gameId);
        $gamePublishers = $serviceGamePublisher->getByGame($gameId);
        $gameTags = $serviceGameTag->getByGame($gameId);
        $gameChangeHistory = $serviceGameChangeHistory->getByGameId($gameId);

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameReviews'] = $gameReviews;
        $bindings['GameGenres'] = $gameGenres;
        $bindings['GameReviewUserList'] = $gameUserReviews;
        $bindings['GameDevelopers'] = $gameDevelopers;
        $bindings['GamePublishers'] = $gamePublishers;
        $bindings['GameTags'] = $gameTags;
        $bindings['GameChangeHistory'] = $gameChangeHistory;

        $bindings['ReleaseDates'] = $serviceGameReleaseDate->getByGame($gameId);
        $bindings['ReleaseDateInfo'] = $serviceGameReleaseDate->getByGameAndRegion($gameId, $regionCode);

        return view('admin.games-detail.show', $bindings);
    }

}