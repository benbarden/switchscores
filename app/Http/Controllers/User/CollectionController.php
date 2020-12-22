<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Services\GamesCollection\PlayStatus;

use App\Traits\SwitchServices;
use App\Traits\AuthUser;
use App\Traits\MemberView;

class CollectionController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesAdd = [
        'game_id' => 'required',
    ];

    public function landing()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Games collection');

        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceQuickReview = $this->getServiceQuickReview();
        $serviceCollectionPlayStatus = new PlayStatus();

        $userId = $this->getAuthId();
        $bindings['UserId'] = $userId;

        $quickReviewGameIdList = $serviceQuickReview->getAllByUserGameIdList($userId);
        $bindings['QuickReviewGameIdList'] = $quickReviewGameIdList;

        $bindings['CollectionStats'] = $serviceCollection->getStats($userId);

        $playStatusList = $serviceCollectionPlayStatus->generateAll();

        $userPlayStatusList = [];

        foreach ($playStatusList as $playStatus) {

            $statusId = $playStatus->getId();
            $listItems = $serviceCollection->getPlayStatusByUser($userId, $statusId);
            $userPlayStatusList[] = ['PlayStatus' => $playStatus, 'ListItems' => $listItems];

        }

        $bindings['UserPlayStatusList'] = $userPlayStatusList;

        return view('user.collection.index', $bindings);
    }

    public function add()
    {
        $bindings = $this->getBindingsCollectionSubpage('Add game to collection');

        $serviceGame = $this->getServiceGame();
        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceCollectionPlayStatus = new PlayStatus();

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

        $bindings['FormMode'] = 'add';

        $bindings['GamesList'] = $serviceGame->getAll();
        $bindings['PlayStatusList'] = $serviceCollectionPlayStatus->generateAll();

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        }

        return view('user.collection.add', $bindings);
    }

    public function edit($itemId)
    {
        $bindings = $this->getBindingsCollectionSubpage('Edit games collection item');

        $serviceCollection = $this->getServiceUserGamesCollection();
        $serviceCollectionPlayStatus = new PlayStatus();

        $request = request();

        $userId = $this->getAuthId();

        $collectionData = $serviceCollection->find($itemId);
        if (!$collectionData) abort(404);

        if ($collectionData->user_id != $userId) abort(403);

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

        $bindings['CollectionData'] = $collectionData;
        $bindings['ItemId'] = $itemId;

        $bindings['PlayStatusList'] = $serviceCollectionPlayStatus->generateAll();

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
