<?php

namespace App\Http\Controllers\User;

use App\Traits\AuthUser;
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
    private $validationRules = [
        'game_id' => 'required',
    ];

    public function landing()
    {
        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceQuickReview = $this->getServiceQuickReview();

        $bindings = [];

        $bindings['TopTitle'] = 'Collection';
        $bindings['PageTitle'] = 'Collection';

        $userId = $this->getAuthId();
        $bindings['UserId'] = $userId;

        $bindings['CollectionList'] = $serviceCollection->getByUser($userId);
        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

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

            $this->validate($request, $this->validationRules);

            $isStarted  = $request->is_started  == 'on' ? 1 : 0;
            $isOngoing  = $request->is_ongoing  == 'on' ? 1 : 0;
            $isComplete = $request->is_complete == 'on' ? 1 : 0;

            $serviceCollection->create(
                $userId, $request->game_id, $request->owned_from, $request->owned_type,
                $isStarted, $isOngoing, $isComplete, $request->hours_played
            );

            return redirect(route('user.collection.landing'));

        }

        $bindings = [];

        $bindings['TopTitle'] = 'User - Games collection - Add game';
        $bindings['PageTitle'] = 'Add game';
        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();

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

            $this->validate($request, $this->validationRules);

            $isStarted  = $request->is_started  == 'on' ? 1 : 0;
            $isOngoing  = $request->is_ongoing  == 'on' ? 1 : 0;
            $isComplete = $request->is_complete == 'on' ? 1 : 0;

            $serviceCollection->edit(
                $collectionData, $request->owned_from, $request->owned_type,
                $isStarted, $isOngoing, $isComplete, $request->hours_played
            );

            return redirect(route('user.collection.landing'));

        } else {

            $bindings['FormMode'] = 'edit';

        }

        $bindings['TopTitle'] = 'User - Games collection - Edit game';
        $bindings['PageTitle'] = 'Edit game';

        $bindings['CollectionData'] = $collectionData;
        $bindings['ItemId'] = $itemId;

        $bindings['GamesList'] = $serviceGame->getAll();

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
