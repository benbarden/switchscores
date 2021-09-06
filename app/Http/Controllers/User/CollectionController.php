<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;
use App\Domain\UserGamesCollection\DbQueries as UserGamesCollectionDbQueries;

use App\Services\GamesCollection\PlayStatus;
use App\Services\UserGamesCollectionService;

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

    protected $repoGame;
    protected $serviceUserGamesCollection;
    protected $repoUserGamesCollection;
    protected $dbUserGamesCollection;

    public function __construct(
        GameRepository $repoGame,
        UserGamesCollectionService $serviceUserGamesCollection,
        UserGamesCollectionRepository $repoUserGamesCollection,
        UserGamesCollectionDbQueries $dbUserGamesCollection
    )
    {
        $this->repoGame = $repoGame;
        $this->serviceUserGamesCollection = $serviceUserGamesCollection;
        $this->repoUserGamesCollection = $repoUserGamesCollection;
        $this->dbUserGamesCollection = $dbUserGamesCollection;
    }

    public function landing()
    {
        $bindings = $this->getBindingsDashboardGenericSubpage('Games collection');

        $serviceCollectionPlayStatus = new PlayStatus();

        $userId = $this->getAuthId();
        $bindings['UserId'] = $userId;

        $quickReviewGameIdList = $this->getServiceQuickReview()->getAllByUserGameIdList($userId);
        $bindings['QuickReviewGameIdList'] = $quickReviewGameIdList;

        $bindings['CollectionStats'] = $this->getServiceUserGamesCollection()->getStats($userId);

        $playStatusList = $serviceCollectionPlayStatus->generateAll();

        $userPlayStatusList = [];

        foreach ($playStatusList as $playStatus) {

            $statusId = $playStatus->getId();
            $listItems = $this->getServiceUserGamesCollection()->getPlayStatusByUser($userId, $statusId);
            $userPlayStatusList[] = ['PlayStatus' => $playStatus, 'ListItems' => $listItems];

        }

        $bindings['UserPlayStatusList'] = $userPlayStatusList;

        // New layout
        $bindings['ListRecentlyAdded'] = $this->getServiceUserGamesCollection()->getByUser($userId, 6);
        $bindings['ListNotStarted'] = $this->getServiceUserGamesCollection()->getNotStartedByUser($userId, 5);
        $bindings['ListPaused'] = $this->getServiceUserGamesCollection()->getPausedByUser($userId, 5);
        $bindings['ListNowPlaying'] = $this->getServiceUserGamesCollection()->getNowPlayingByUser($userId, 5);
        $bindings['ListReplaying'] = $this->getServiceUserGamesCollection()->getReplayingByUser($userId, 5);
        $bindings['ListCompleted'] = $this->getServiceUserGamesCollection()->getCompletedByUser($userId, 5);
        $bindings['ListAbandoned'] = $this->getServiceUserGamesCollection()->getAbandonedByUser($userId, 5);
        $bindings['ListEndless'] = $this->getServiceUserGamesCollection()->getEndlessByUser($userId, 5);

        $bindings['TotalNotStarted'] = $this->getServiceUserGamesCollection()->getUserTotalNotStarted($userId);
        $bindings['TotalPaused'] = $this->getServiceUserGamesCollection()->getUserTotalPaused($userId);
        $bindings['TotalNowPlaying'] = $this->getServiceUserGamesCollection()->getUserTotalNowPlaying($userId);
        $bindings['TotalReplaying'] = $this->getServiceUserGamesCollection()->getUserTotalReplaying($userId);
        $bindings['TotalCompleted'] = $this->getServiceUserGamesCollection()->getUserTotalCompleted($userId);
        $bindings['TotalAbandoned'] = $this->getServiceUserGamesCollection()->getUserTotalAbandoned($userId);
        $bindings['TotalEndless'] = $this->getServiceUserGamesCollection()->getUserTotalEndless($userId);

        $bindings['TotalGames'] = $this->getServiceUserGamesCollection()->getUserTotalGames($userId);
        $bindings['TotalHours'] = $this->getServiceUserGamesCollection()->getUserTotalHours($userId);

        return view('user.collection.index', $bindings);
    }

    public function categoryBreakdown()
    {
        $bindings = $this->getBindingsCollectionSubpage('Category breakdown');

        $bindings['CategoryBreakdown'] = $this->dbUserGamesCollection->getCategoryBreakdown($this->getAuthId());

        return view('user.collection.category-breakdown', $bindings);
    }

    public function topRatedByCategory($categoryId)
    {
        $category = $this->getServiceCategory()->find($categoryId);
        if (!$category) abort(404);

        $bindings = $this->getBindingsCollectionSubpage('Category breakdown: '.$category->name);

        $bindings['Category'] = $category;
        $bindings['RankedGameList'] = $this->getServiceCategory()->getRankedByCategory($category->id);
        $bindings['OwnedGamedIdList'] = $this->getServiceUserGamesCollection()->getGameIdsByUser($this->getAuthId());

        return view('user.collection.top-rated-by-category', $bindings);
    }

    public function showList($listOption)
    {
        $userId = $this->getAuthId();
        $tableSort = '[4, "desc"]';

        switch ($listOption) {
            case PlayStatus::PLAY_STATUS_NOT_STARTED:
                $pageTitle = 'Not started';
                $collectionList = $this->getServiceUserGamesCollection()->getNotStartedByUser($userId);
                break;
            case PlayStatus::PLAY_STATUS_PAUSED:
                $pageTitle = 'Paused';
                $collectionList = $this->getServiceUserGamesCollection()->getPausedByUser($userId);
                break;
            case 'active':
                $pageTitle = 'Active';
                $collectionNowPlaying = $this->getServiceUserGamesCollection()->getNowPlayingByUser($userId);
                $collectionReplaying = $this->getServiceUserGamesCollection()->getReplayingByUser($userId);
                $collectionList = $collectionNowPlaying->merge($collectionReplaying);
                break;
            case PlayStatus::PLAY_STATUS_COMPLETED:
                $pageTitle = 'Completed';
                $collectionList = $this->getServiceUserGamesCollection()->getCompletedByUser($userId);
                break;
            case PlayStatus::PLAY_STATUS_ABANDONED:
                $pageTitle = 'Abandoned';
                $collectionList = $this->getServiceUserGamesCollection()->getAbandonedByUser($userId);
                break;
            case PlayStatus::PLAY_STATUS_ENDLESS:
                $pageTitle = 'Endless';
                $collectionList = $this->getServiceUserGamesCollection()->getEndlessByUser($userId);
                break;
            case 'recently-added':
                $pageTitle = 'All games in your collection';
                $collectionList = $this->getServiceUserGamesCollection()->getByUser($userId);
                break;
            default:
                abort(404);
        }

        $bindings = $this->getBindingsCollectionSubpage($pageTitle, $tableSort);

        $bindings['CollectionList'] = $collectionList;
        $bindings['UserId'] = $userId;

        $quickReviewGameIdList = $this->getServiceQuickReview()->getAllByUserGameIdList($userId);
        $bindings['QuickReviewGameIdList'] = $quickReviewGameIdList;

        return view('user.collection.list', $bindings);
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

            $gameId = $request->game_id;

            $validator = Validator::make($request->all(), $this->validationRulesAdd);

            $validator->after(function ($validator) use ($userId, $gameId) {
                // Check for duplicates
                if ($this->getServiceUserGamesCollection()->isGameInCollection($userId, $gameId)) {
                    $validator->errors()->add('title', 'This game is already in your collection.');
                }
            });

            if ($validator->fails()) {
                return redirect(route('user.collection.add'))
                    ->withErrors($validator)
                    ->withInput();
            }

            $serviceCollection->create(
                $userId, $request->game_id, $request->owned_from, $request->owned_type,
                $request->hours_played, $request->play_status
            );

            return redirect(route('user.collection.list', ['listOption' => 'recently-added']));

        }

        $bindings['FormMode'] = 'add';

        //$bindings['GamesList'] = $serviceGame->getAll();
        $bindings['PlayStatusList'] = $serviceCollectionPlayStatus->generateAll();

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        } else {
            abort(404);
        }

        $gameData = $this->repoGame->find($urlGameId);
        $bindings['SelectedGameTitle'] = $gameData->title;

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

            return redirect(route('user.collection.list', ['listOption' => 'recently-added']));

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
