<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;

use App\Traits\WosServices;
use App\Traits\SiteRequestData;

class GamesDetailController extends Controller
{
    use WosServices;
    use SiteRequestData;

    public function show($gameId)
    {
        $regionCode = $this->getRegionCode();

        $serviceGame = $this->getServiceGame();
        $serviceReviewLink = $this->getServiceReviewLink();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceGameGenres = $this->getServiceGameGenre();
        $serviceGameTag = $this->getServiceGameTag();
        $serviceReviewUser = $this->getServiceReviewUser();
        $serviceGameDeveloper = $this->getServiceGameDeveloper();
        $serviceGamePublisher = $this->getServiceGamePublisher();
        $serviceGameChangeHistory = $this->getServiceGameChangeHistory();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

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

        $selectedTabId = \Request::get('tabid');
        $bindings['SelectedTabId'] = $selectedTabId;

        // Get all the data
        $gameReviews = $serviceReviewLink->getByGame($gameId);
        $gameGenres = $serviceGameGenres->getByGame($gameId);
        $gameUserReviews = $serviceReviewUser->getActiveByGame($gameId);
        $gameDevelopers = $serviceGameDeveloper->getByGame($gameId);
        $gamePublishers = $serviceGamePublisher->getByGame($gameId);
        $gameTags = $serviceGameTag->getByGame($gameId);
        $gameChangeHistory = $serviceGameChangeHistory->getByGameId($gameId);
        $gameTitleHashes = $serviceGameTitleHash->getByGameId($gameId);

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['GameReviews'] = $gameReviews;
        $bindings['GameGenres'] = $gameGenres;
        $bindings['GameReviewUserList'] = $gameUserReviews;
        $bindings['GameDevelopers'] = $gameDevelopers;
        $bindings['GamePublishers'] = $gamePublishers;
        $bindings['GameTags'] = $gameTags;
        $bindings['GameChangeHistory'] = $gameChangeHistory;
        $bindings['GameTitleHashes'] = $gameTitleHashes;

        $bindings['ReleaseDates'] = $serviceGameReleaseDate->getByGame($gameId);
        $bindings['ReleaseDateInfo'] = $serviceGameReleaseDate->getByGameAndRegion($gameId, $regionCode);

        // Audit data
        $gameAuditsCore = $game->audits()->orderBy('created_at', 'desc')->get();
        $bindings['GameAuditsCore'] = $gameAuditsCore;

        return view('admin.games-detail.show', $bindings);
    }

}