<?php

namespace App\Http\Controllers\User;

use App\Traits\AuthUser;
use App\UserGamesCollection;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Traits\SwitchServices;

class CollectionController extends Controller
{
    use SwitchServices;
    use AuthUser;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesAdd = [
        'game_id' => 'required',
    ];

    public function landing()
    {
        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceQuickReview = $this->getServiceQuickReview();

        $bindings = [];

        $bindings['TopTitle'] = 'Games collection';
        $bindings['PageTitle'] = 'Games collection';

        $userId = $this->getAuthId();
        $bindings['UserId'] = $userId;

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        $bindings['CollectionNowPlaying'] = $serviceCollection->getByUserAndPlayStatus($userId, UserGamesCollection::PLAY_STATUS_NOW_PLAYING);
        $bindings['CollectionPaused'] = $serviceCollection->getByUserAndPlayStatus($userId, UserGamesCollection::PLAY_STATUS_PAUSED);
        $bindings['CollectionNotStarted'] = $serviceCollection->getByUserAndPlayStatus($userId, UserGamesCollection::PLAY_STATUS_NOT_STARTED);
        $bindings['CollectionAbandoned'] = $serviceCollection->getByUserAndPlayStatus($userId, UserGamesCollection::PLAY_STATUS_ABANDONED);
        $bindings['CollectionCompleted'] = $serviceCollection->getByUserAndPlayStatus($userId, UserGamesCollection::PLAY_STATUS_COMPLETED);

        $quickReviewGameIdList = $serviceQuickReview->getAllByUserGameIdList($userId);
        $bindings['QuickReviewGameIdList'] = $quickReviewGameIdList;

        return view('user.collection.index', $bindings);
    }

    public function add()
    {
        $serviceGame = $this->getServiceGame();
        $serviceCollection = $this->getServiceUserGamesCollection();

        $request = request();

        $userId = $this->getAuthId();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesAdd);

            $serviceCollection->create(
                $userId, $request->game_id, $request->owned_from, $request->owned_type,
                $request->hours_played, $request->play_status
            );

            return redirect(route('user.collection.landing'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'User - Games collection - Add game';
        $bindings['PageTitle'] = 'Add game to collection';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();
        $bindings['PlayStatusList'] = $serviceCollection->getPlayStatusList();

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        }

        return view('user.collection.add', $bindings);
    }

    public function edit($itemId)
    {
        $serviceGame = $this->getServiceGame();
        $serviceCollection = $this->getServiceUserGamesCollection();

        $request = request();

        $userId = $this->getAuthId();

        $collectionData = $serviceCollection->find($itemId);
        if (!$collectionData) abort(404);

        if ($collectionData->user_id != $userId) abort(403);

        $bindings = [];

        if ($request->isMethod('post')) {

            $bindings['FormMode'] = 'edit-post';

            //$this->validate($request, $this->validationRules);

            $serviceCollection->edit(
                $collectionData, $request->owned_from, $request->owned_type,
                $request->hours_played, $request->play_status
            );

            return redirect(route('user.collection.landing'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'User - Games collection - Edit game';
        $bindings['PageTitle'] = 'Edit games collection';

        $bindings['CollectionData'] = $collectionData;
        $bindings['ItemId'] = $itemId;

        $bindings['PlayStatusList'] = $serviceCollection->getPlayStatusList();

        return view('user.collection.edit', $bindings);
    }

    public function delete()
    {
        $serviceCollection = $this->getServiceUserGamesCollection();

        $request = request();

        $collectionItemId = $request->itemId;

        if (!$collectionItemId) {
            return response()->json(['error' => 'Missing data: itemId'], 400);
        }

        $collectionItem = $serviceCollection->find($collectionItemId);

        if (!$collectionItem) {
            return response()->json(['error' => 'Not found: '.$collectionItemId], 404);
        }

        if ($collectionItem->user_id != $this->getAuthId()) {
            return response()->json(['error' => 'Collection item belongs to another user'], 400);
        }

        // Delete from collection
        $serviceCollection->delete($collectionItemId);

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
