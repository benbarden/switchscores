<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as Controller;
use App\Services\ServiceContainer;

use Auth;

class StatsController extends Controller
{
    public function landing()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $bindings = [];

        $bindings['PageTitle'] = 'Stats';
        $bindings['TopTitle'] = 'Admin - Stats';

        return view('admin.stats.landing', $bindings);
    }

    public function reviewSite()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $bindings = [];

        $serviceReviewLinks = $serviceContainer->getReviewLinkService();
        $serviceReviewSite = $serviceContainer->getReviewSiteService();
        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();
        $serviceTopRated = $serviceContainer->getTopRatedService();
        $serviceReviewStats = $serviceContainer->getReviewStatsService();

        $bindings['RankedGameCount'] = $serviceTopRated->getCount($regionCode);
        $bindings['UnrankedGameCount'] = $serviceTopRated->getUnrankedCount($regionCode);

        $releasedGameCount = $serviceGameReleaseDate->countReleased($regionCode);
        $reviewLinkCount = $serviceReviewLinks->countActive();

        $bindings['ReleasedGameCount'] = $releasedGameCount;
        $bindings['ReviewLinkCount'] = $reviewLinkCount;

        $reviewSitesActive = $serviceReviewSite->getActive();
        $reviewSitesRender = [];

        foreach ($reviewSitesActive as $reviewSite) {

            $id = $reviewSite->id;
            $name = $reviewSite->name;
            $linkTitle = $reviewSite->link_title;

            $reviewCount = $serviceReviewLinks->countBySite($id);
            $reviewLatest = $serviceReviewLinks->getLatestBySite($id, 1);
            if (count($reviewLatest) > 0) {
                $latestReviewDate = $reviewLatest[0]['review_date'];
            } else {
                $latestReviewDate = null;
            }
            $reviewLinkContribTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $reviewLinkCount);
            $reviewGameCompletionTotal = $serviceReviewStats->calculateContributionPercentage($reviewCount, $releasedGameCount);

            $reviewSitesRender[] = [
                'id' => $id,
                'name' => $name,
                'link_title' => $linkTitle,
                'review_count' => $reviewCount,
                'review_link_contrib_total' => $reviewLinkContribTotal,
                'review_game_completion_total' => $reviewGameCompletionTotal,
                'latest_review_date' => $latestReviewDate,
            ];

        }

        $bindings['ReviewSitesArray'] = $reviewSitesRender;

        $bindings['PageTitle'] = 'Review site stats';
        $bindings['TopTitle'] = 'Admin - Stats - Review sites';

        return view('admin.stats.review.site', $bindings);
    }

    public function oldDeveloperMultiple()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldDevelopersMultiple();

        $bindings['PageTitle'] = 'Old developers - multiple records';
        $bindings['TopTitle'] = 'Admin - Stats - Old developers - multiple records';

        return view('admin.stats.games.old-developer-multiple', $bindings);
    }

    public function oldPublisherMultiple()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldPublishersMultiple();

        $bindings['PageTitle'] = 'Old publishers - multiple records';
        $bindings['TopTitle'] = 'Admin - Stats - Old publishers - multiple records';

        return view('admin.stats.games.old-publisher-multiple', $bindings);
    }

    public function oldDeveloperByCount()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldDevelopersByCount();

        $bindings['PageTitle'] = 'Old developers - by count';
        $bindings['TopTitle'] = 'Admin - Stats - Old developers - by count';

        return view('admin.stats.games.old-developer-by-count', $bindings);
    }

    public function oldPublisherByCount()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $serviceGame = $serviceContainer->getGameService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getOldPublishersByCount();

        $bindings['PageTitle'] = 'Old publishers - by count';
        $bindings['TopTitle'] = 'Admin - Stats - Old publishers - by count';

        return view('admin.stats.games.old-publisher-by-count', $bindings);
    }

    public function oldDeveloperGameList($developer)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $serviceDeveloper = $serviceContainer->getDeveloperService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getByDeveloper($developer);

        $bindings['DeveloperName'] = $developer;
        $developerData = $serviceDeveloper->getByName($developer);
        if ($developerData) {
            $bindings['DeveloperData'] = $developerData;
        }

        $bindings['PageTitle'] = 'Old developers - Game list';
        $bindings['TopTitle'] = 'Admin - Stats - Old developers - Game list';

        return view('admin.stats.games.old-developer-game-list', $bindings);
    }

    public function oldPublisherGameList($publisher)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $serviceGame = $serviceContainer->getGameService();
        $servicePublisher = $serviceContainer->getPublisherService();

        $bindings = [];

        $bindings['ItemList'] = $serviceGame->getByPublisher($publisher);

        $bindings['PublisherName'] = $publisher;
        $publisherData = $servicePublisher->getByName($publisher);
        if ($publisherData) {
            $bindings['PublisherData'] = $publisherData;
        }

        $bindings['PageTitle'] = 'Old publishers - by count';
        $bindings['TopTitle'] = 'Admin - Stats - Old publishers - by count';

        return view('admin.stats.games.old-publisher-game-list', $bindings);
    }

    public function clearOldDeveloperField()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGame = $serviceContainer->getGameService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $serviceGame->clearOldDeveloperField($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function clearOldPublisherField()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceGame = $serviceContainer->getGameService();
        $serviceUser = $serviceContainer->getUserService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $gameId = $request->gameId;
        if (!$gameId) {
            return response()->json(['error' => 'Missing data: gameId'], 400);
        }

        $game = $serviceGame->find($gameId);
        if (!$gameId) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $serviceGame->clearOldPublisherField($game);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addAllNewDevelopers()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceUser = $serviceContainer->getUserService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceDeveloper = $serviceContainer->getDeveloperService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $developerName = $request->developerName;
        if (!$developerName) {
            return response()->json(['error' => 'Missing data: developerName'], 400);
        }

        $developerData = $serviceDeveloper->getByName($developerName);
        if (!$developerData) {
            return response()->json(['error' => 'No developer record found'], 400);
        }

        $developerId = $developerData->id;

        $gamesWithOldDeveloper = $serviceGame->getByDeveloper($developerName);
        foreach ($gamesWithOldDeveloper as $game) {

            $gameId = $game->id;

            if ($serviceGameDeveloper->gameHasDeveloper($gameId, $developerId)) {
                continue;
            }

            $serviceGameDeveloper->createGameDeveloper($gameId, $developerId);

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeAllOldDevelopers()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceUser = $serviceContainer->getUserService();
        $serviceGame = $serviceContainer->getGameService();
        $serviceDeveloper = $serviceContainer->getDeveloperService();
        $serviceGameDeveloper = $serviceContainer->getGameDeveloperService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $developerName = $request->developerName;
        if (!$developerName) {
            return response()->json(['error' => 'Missing data: developerName'], 400);
        }

        $developerData = $serviceDeveloper->getByName($developerName);
        if (!$developerData) {
            return response()->json(['error' => 'No developer record found'], 400);
        }

        $developerId = $developerData->id;

        $gamesWithOldDeveloper = $serviceGame->getByDeveloper($developerName);
        foreach ($gamesWithOldDeveloper as $game) {

            $gameId = $game->id;

            if (!$serviceGameDeveloper->gameHasDeveloper($gameId, $developerId)) {
                // Failsafe for games that might not have the new record assigned yet
                continue;
            }

            $serviceGame->clearOldDeveloperField($game);

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function addAllNewPublishers()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceUser = $serviceContainer->getUserService();
        $serviceGame = $serviceContainer->getGameService();
        $servicePublisher = $serviceContainer->getPublisherService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $publisherName = $request->publisherName;
        if (!$publisherName) {
            return response()->json(['error' => 'Missing data: publisherName'], 400);
        }

        $publisherData = $servicePublisher->getByName($publisherName);
        if (!$publisherData) {
            return response()->json(['error' => 'No publisher record found'], 400);
        }

        $publisherId = $publisherData->id;

        $gamesWithOldPublisher = $serviceGame->getByPublisher($publisherName);
        foreach ($gamesWithOldPublisher as $game) {

            $gameId = $game->id;

            if (!$serviceGamePublisher->gameHasPublisher($gameId, $publisherId)) {
                $serviceGamePublisher->createGamePublisher($gameId, $publisherId);
            }

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }

    public function removeAllOldPublishers()
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */
        $serviceUser = $serviceContainer->getUserService();
        $serviceGame = $serviceContainer->getGameService();
        $servicePublisher = $serviceContainer->getPublisherService();
        $serviceGamePublisher = $serviceContainer->getGamePublisherService();

        $userId = Auth::id();

        $user = $serviceUser->find($userId);
        if (!$user) {
            return response()->json(['error' => 'Cannot find user!'], 400);
        }

        $request = request();

        $publisherName = $request->publisherName;
        if (!$publisherName) {
            return response()->json(['error' => 'Missing data: publisherName'], 400);
        }

        $publisherData = $servicePublisher->getByName($publisherName);
        if (!$publisherData) {
            return response()->json(['error' => 'No publisher record found'], 400);
        }

        $publisherId = $publisherData->id;

        $gamesWithOldPublisher = $serviceGame->getByPublisher($publisherName);
        foreach ($gamesWithOldPublisher as $game) {

            $gameId = $game->id;

            if ($serviceGamePublisher->gameHasPublisher($gameId, $publisherId)) {
                // Failsafe for games that might not have the new record assigned yet
                $serviceGame->clearOldPublisherField($game);
            }

        }

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
