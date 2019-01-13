<?php

namespace App\Http\Controllers\Admin;

use App\FeedItemGame;
use App\Http\Controllers\BaseController;
use App\Services\HtmlLoader\Wikipedia\Importer;
use Illuminate\Http\Request;

class FeedItemGameController extends BaseController
{
    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function showList($report = null)
    {
        $bindings = array();

        $bindings['TopTitle'] = 'Admin - Feed items - Games';
        $bindings['PageTitle'] = 'Feed items - Games';

        $feedItemGameService = $this->serviceContainer->getFeedItemGameService();

        if ($report == null) {
            $bindings['ActiveNav'] = '';
            $feedItems = $feedItemGameService->getPending();
            $jsInitialSort = "[ 0, 'asc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'pending-game-id':
                    $feedItems = $feedItemGameService->getPendingWithGameId();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'pending-no-game-id':
                    $feedItems = $feedItemGameService->getPendingNoGameId();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'ok-to-update':
                    $feedItems = $feedItemGameService->getForProcessing();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'complete':
                    $feedItems = $feedItemGameService->getComplete();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'inactive':
                    $feedItems = $feedItemGameService->getInactive();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('admin.feed-items.games.list', $bindings);
    }

    public function edit($itemId)
    {
        $regionCode = \Request::get('regionCode');

        $feedItemGameService = $this->serviceContainer->getFeedItemGameService();
        $gameService = $this->serviceContainer->getGameService();
        $gameReleaseDateService = $this->serviceContainer->getGameReleaseDateService();
        $gameTitleHashService = $this->serviceContainer->getGameTitleHashService();

        $feedItemData = $feedItemGameService->find($itemId);
        if (!$feedItemData) abort(404);

        $request = request();
        $bindings = [];

        $statusList = [];

        $statusCode = FeedItemGame::STATUS_PENDING;
        $statusList[] = ['id' => $statusCode, 'title' => $feedItemGameService->getStatusDesc($statusCode)];
        $statusCode = FeedItemGame::STATUS_OK_TO_UPDATE;
        $statusList[] = ['id' => $statusCode, 'title' => $feedItemGameService->getStatusDesc($statusCode)];
        $statusCode = FeedItemGame::STATUS_COMPLETE;
        $statusList[] = ['id' => $statusCode, 'title' => $feedItemGameService->getStatusDesc($statusCode)];
        $statusCode = FeedItemGame::STATUS_NO_UPDATE_NEEDED;
        $statusList[] = ['id' => $statusCode, 'title' => $feedItemGameService->getStatusDesc($statusCode)];
        $statusCode = FeedItemGame::STATUS_SKIPPED_BY_USER;
        $statusList[] = ['id' => $statusCode, 'title' => $feedItemGameService->getStatusDesc($statusCode)];
        $statusCode = FeedItemGame::STATUS_SKIPPED_BY_GAME_RULES;
        $statusList[] = ['id' => $statusCode, 'title' => $feedItemGameService->getStatusDesc($statusCode)];

        $bindings['StatusList'] = $statusList;

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            if ($request->game_id && !$feedItemData->game_id) {

                $gameId = $request->game_id;

                // If assigning a game id, we need to set the modified fields
                $importer = new Importer();
                $game = $gameService->find($gameId);
                $gameReleaseDates = $gameReleaseDateService->getByGame($gameId);
                $modifiedFields = $importer->getGameModifiedFields($feedItemData, $game, $gameReleaseDates);
                $feedItemData->modified_fields = serialize($modifiedFields);

                // We also need to add a new title hash if one doesn't already exist
                $titleHash = $gameTitleHashService->generateHash($feedItemData->item_title);
                $existingHash = $gameTitleHashService->getByHash($titleHash);
                if (!$existingHash) {
                    $gameTitleHashService->create($feedItemData->item_title, $titleHash, $gameId);
                }

            }

            // Update the DB
            $feedItemGameService->edit(
                $feedItemData, $request->game_id, $request->status_code
            );

            // All done; send us back
            return redirect(route('admin.feed-items.games.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Admin - Feed items - Games - Edit';
        $bindings['PageTitle'] = 'Edit feed item';
        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $gameService->getAll($regionCode);

        // Load existing game data
        if ($feedItemData->game_id) {
            $gameId = $feedItemData->game_id;
            $game = $gameService->find($gameId);
            $gameReleaseDates = $gameReleaseDateService->getByGame($gameId);
            $bindings['GameData'] = $game;
            foreach ($gameReleaseDates as $gameReleaseDate) {
                $region = strtoupper($gameReleaseDate->region);
                $bindings['ReleaseDates'.$region] = $gameReleaseDate;
            }
        }

        return view('admin.feed-items.games.edit', $bindings);
    }
}
