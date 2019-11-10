<?php

namespace App\Http\Controllers\Staff\Wikipedia;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SiteRequestData;
use App\Traits\WosServices;

use App\FeedItemGame;
use App\Services\HtmlLoader\Wikipedia\Importer;

class WikiUpdatesController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use WosServices;
    use SiteRequestData;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function showList($report = null)
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Wiki updates';
        $bindings['PageTitle'] = 'Wiki updates';

        $serviceFeedItemGame = $this->getServiceFeedItemGame();

        if ($report == null) {
            $bindings['ActiveNav'] = '';
            $feedItems = $serviceFeedItemGame->getPendingAndOkToUpdate();
            $jsInitialSort = "[ 0, 'asc']";
        } else {
            $bindings['ActiveNav'] = $report;
            switch ($report) {
                case 'pending-game-id':
                    $feedItems = $serviceFeedItemGame->getPendingWithGameId();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'pending-no-game-id':
                    $feedItems = $serviceFeedItemGame->getPendingNoGameId();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'ok-to-update':
                    $feedItems = $serviceFeedItemGame->getForProcessing();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'complete':
                    $feedItems = $serviceFeedItemGame->getComplete();
                    $jsInitialSort = "[ 0, 'asc']";
                    break;
                case 'inactive':
                    $feedItems = $serviceFeedItemGame->getInactive();
                    $jsInitialSort = "[ 0, 'desc']";
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        $bindings['FeedItems'] = $feedItems;
        $bindings['jsInitialSort'] = $jsInitialSort;

        return view('staff.wikipedia.wiki-updates.list', $bindings);
    }

    public function edit($itemId)
    {
        $regionCode = $this->getRegionCode();

        $serviceFeedItemGame = $this->getServiceFeedItemGame();
        $serviceGame = $this->getServiceGame();
        $serviceGameReleaseDate = $this->getServiceGameReleaseDate();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        $feedItemData = $serviceFeedItemGame->find($itemId);
        if (!$feedItemData) abort(404);

        $request = request();
        $bindings = [];

        $statusPending = [
            'id' => FeedItemGame::STATUS_PENDING,
            'title' => $serviceFeedItemGame->getStatusDesc(FeedItemGame::STATUS_PENDING)
        ];
        $statusOkToUpdate = [
            'id' => FeedItemGame::STATUS_OK_TO_UPDATE,
            'title' => $serviceFeedItemGame->getStatusDesc(FeedItemGame::STATUS_OK_TO_UPDATE)
        ];
        $statusComplete = [
            'id' => FeedItemGame::STATUS_COMPLETE,
            'title' => $serviceFeedItemGame->getStatusDesc(FeedItemGame::STATUS_COMPLETE)
        ];
        $statusNoUpdateNeeded = [
            'id' => FeedItemGame::STATUS_NO_UPDATE_NEEDED,
            'title' => $serviceFeedItemGame->getStatusDesc(FeedItemGame::STATUS_NO_UPDATE_NEEDED)
        ];
        $statusSkippedByUser = [
            'id' => FeedItemGame::STATUS_SKIPPED_BY_USER,
            'title' => $serviceFeedItemGame->getStatusDesc(FeedItemGame::STATUS_SKIPPED_BY_USER)
        ];
        $statusSkippedByGameRules = [
            'id' => FeedItemGame::STATUS_SKIPPED_BY_GAME_RULES,
            'title' => $serviceFeedItemGame->getStatusDesc(FeedItemGame::STATUS_SKIPPED_BY_GAME_RULES)
        ];

        $statusList = [
            $statusPending, $statusOkToUpdate, $statusComplete,
            $statusNoUpdateNeeded, $statusSkippedByUser, $statusSkippedByGameRules,
        ];
        $quickStatusList = [
            $statusPending, $statusOkToUpdate, $statusSkippedByUser,
        ];

        $bindings['StatusList'] = $statusList;
        $bindings['QuickStatusList'] = $quickStatusList;

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            $this->validate($request, $this->validationRules);

            if ($request->game_id && !$feedItemData->game_id) {

                $gameId = $request->game_id;

                // If assigning a game id, we need to set the modified fields
                $importer = new Importer();
                $game = $serviceGame->find($gameId);
                $gameReleaseDates = $serviceGameReleaseDate->getByGame($gameId);
                $modifiedFields = $importer->getGameModifiedFields($feedItemData, $game, $gameReleaseDates);
                $feedItemData->modified_fields = serialize($modifiedFields);

                // We also need to add a new title hash if one doesn't already exist
                $titleHash = $serviceGameTitleHash->generateHash($feedItemData->item_title);
                $existingHash = $serviceGameTitleHash->getByHash($titleHash);
                if (!$existingHash) {
                    $serviceGameTitleHash->create($feedItemData->item_title, $titleHash, $gameId);
                }

            }

            // Update the DB
            $serviceFeedItemGame->edit(
                $feedItemData, $request->game_id, $request->status_code
            );

            // All done; send us back
            return redirect(route('staff.wikipedia.wiki-updates.list'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Wiki updates - Edit';
        $bindings['PageTitle'] = 'Edit wiki update';
        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $serviceGame->getAll($regionCode);

        // Load existing game data
        if ($feedItemData->game_id) {
            $gameId = $feedItemData->game_id;
            $game = $serviceGame->find($gameId);
            $gameReleaseDates = $serviceGameReleaseDate->getByGame($gameId);
            $bindings['GameData'] = $game;
            foreach ($gameReleaseDates as $gameReleaseDate) {
                $region = strtoupper($gameReleaseDate->region);
                $bindings['ReleaseDates'.$region] = $gameReleaseDate;
            }
        }

        return view('staff.wikipedia.wiki-updates.edit', $bindings);
    }
}
