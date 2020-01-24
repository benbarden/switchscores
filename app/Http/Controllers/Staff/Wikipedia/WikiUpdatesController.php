<?php

namespace App\Http\Controllers\Staff\Wikipedia;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\FeedItemGame;
use App\GameImportRuleWikipedia;
use App\Services\HtmlLoader\Wikipedia\Importer;

use App\Traits\SwitchServices;

class WikiUpdatesController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
    ];

    public function showListAllPending()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Wiki updates: All pending';
        $bindings['PageTitle'] = 'Wiki updates: All pending';

        $bindings['ActiveNav'] = 'all-pending';
        $bindings['FeedItems'] = $this->getServiceFeedItemGame()->getPending();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('staff.wikipedia.wiki-updates.list', $bindings);
    }

    public function showListNoGameId()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Wiki updates: Pending, no game ID';
        $bindings['PageTitle'] = 'Wiki updates: Pending, no game ID';

        $bindings['ActiveNav'] = 'pending-no-game-id';
        $bindings['FeedItems'] = $this->getServiceFeedItemGame()->getPendingNoGameId();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('staff.wikipedia.wiki-updates.list', $bindings);
    }

    public function showListWithGameId()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Wiki updates: Pending, with game ID';
        $bindings['PageTitle'] = 'Wiki updates: Pending, with game ID';

        $bindings['ActiveNav'] = 'pending-with-game-id';
        $bindings['FeedItems'] = $this->getServiceFeedItemGame()->getPendingWithGameId();
        $bindings['jsInitialSort'] = "[ 0, 'asc']";

        return view('staff.wikipedia.wiki-updates.list', $bindings);
    }

    public function showListAllComplete()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Wiki updates: All complete';
        $bindings['PageTitle'] = 'Wiki updates: All complete';

        $bindings['ActiveNav'] = 'all-complete';
        $bindings['FeedItems'] = $this->getServiceFeedItemGame()->getComplete();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.wikipedia.wiki-updates.list', $bindings);
    }

    public function showListAllInactive()
    {
        $bindings = [];

        $bindings['TopTitle'] = 'Wiki updates: All inactive';
        $bindings['PageTitle'] = 'Wiki updates: All inactive';

        $bindings['ActiveNav'] = 'all-inactive';
        $bindings['FeedItems'] = $this->getServiceFeedItemGame()->getInactive();
        $bindings['jsInitialSort'] = "[ 0, 'desc']";

        return view('staff.wikipedia.wiki-updates.list', $bindings);
    }

    public function edit($itemId)
    {
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

                // Get game import rule
                $gameImportRule = $this->getServiceGameImportRuleWikipedia()->getByGameId($gameId);
                if (!$gameImportRule) {
                    $gameImportRule = new GameImportRuleWikipedia;
                }

                // If assigning a game id, we need to set the modified fields
                $importer = new Importer();
                $game = $serviceGame->find($gameId);
                $modifiedFields = $importer->getGameModifiedFields($feedItemData, $game, $gameImportRule);
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
            return redirect(route('staff.wikipedia.wiki-updates.list-all-pending'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'Wiki updates - Edit';
        $bindings['PageTitle'] = 'Edit wiki update';
        $bindings['FeedItemData'] = $feedItemData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $serviceGame->getAll();

        // Load existing game data
        if ($feedItemData->game_id) {
            $gameId = $feedItemData->game_id;
            $game = $serviceGame->find($gameId);
            $bindings['GameData'] = $game;
            $bindings['GameImportRule'] = $this->getServiceGameImportRuleWikipedia()->getByGameId($gameId);
        }

        return view('staff.wikipedia.wiki-updates.edit', $bindings);
    }
}
