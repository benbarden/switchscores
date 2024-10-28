<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\Category\DbQueries as CategoryDbQueries;
use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;
use App\Domain\UserGamesCollection\PlayStatus as UserGamesCollectionPlayStatus;
use App\Domain\UserGamesCollection\DbQueries as UserGamesCollectionDbQueries;
use App\Domain\UserGamesCollection\CollectionStatsRepository;

use App\Events\GameCollectionAdded;
use App\Events\GameCollectionRemoved;

use App\Services\UserGamesCollectionService;

use App\Traits\SwitchServices;

class CollectionController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesAdd = [
        'game_id' => 'required',
    ];

    public function __construct(
        private GameRepository $repoGame,
        private CategoryRepository $repoCategory,
        private CategoryDbQueries $dbCategory,
        private UserGamesCollectionService $serviceUserGamesCollection,
        private UserGamesCollectionRepository $repoUserGamesCollection,
        private UserGamesCollectionPlayStatus $ugcPlayStatus,
        private UserGamesCollectionDbQueries $dbUserGamesCollection,
        private CollectionStatsRepository $repoCollectionStats
    )
    {
    }

    public function landing()
    {
        $pageTitle = 'Games collection';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;
        $bindings['UserId'] = $userId;

        $quickReviewGameIdList = $this->getServiceQuickReview()->getAllByUserGameIdList($userId);
        $bindings['QuickReviewGameIdList'] = $quickReviewGameIdList;

        $bindings['CollectionStats'] = $this->repoCollectionStats->userStats($userId);

        $playStatusList = $this->ugcPlayStatus->generateAll();

        $userPlayStatusList = [];

        foreach ($playStatusList as $playStatus) {

            $statusId = $playStatus->getId();
            $listItems = $this->repoUserGamesCollection->byUserAndPlayStatus($userId, $statusId);
            $userPlayStatusList[] = ['PlayStatus' => $playStatus, 'ListItems' => $listItems];

        }

        $bindings['UserPlayStatusList'] = $userPlayStatusList;

        // New layout
        $bindings['ListRecentlyAdded'] = $this->repoUserGamesCollection->byUser($userId, 6);
        $bindings['ListNotStarted'] = $this->repoUserGamesCollection->byUserNotStarted($userId, 5);
        $bindings['ListPaused'] = $this->repoUserGamesCollection->byUserPaused($userId, 5);
        $bindings['ListNowPlaying'] = $this->repoUserGamesCollection->byUserNowPlaying($userId, 5);
        $bindings['ListReplaying'] = $this->repoUserGamesCollection->byUserReplaying($userId, 5);
        $bindings['ListCompleted'] = $this->repoUserGamesCollection->byUserCompleted($userId, 5);
        $bindings['ListAbandoned'] = $this->repoUserGamesCollection->byUserAbandoned($userId, 5);
        $bindings['ListEndless'] = $this->repoUserGamesCollection->byUserEndless($userId, 5);

        $bindings['TotalNotStarted'] = $this->repoCollectionStats->userTotalNotStarted($userId);
        $bindings['TotalPaused'] = $this->repoCollectionStats->userTotalPaused($userId);
        $bindings['TotalNowPlaying'] = $this->repoCollectionStats->userTotalNowPlaying($userId);
        $bindings['TotalReplaying'] = $this->repoCollectionStats->userTotalReplaying($userId);
        $bindings['TotalCompleted'] = $this->repoCollectionStats->userTotalCompleted($userId);
        $bindings['TotalAbandoned'] = $this->repoCollectionStats->userTotalAbandoned($userId);
        $bindings['TotalEndless'] = $this->repoCollectionStats->userTotalEndless($userId);

        $bindings['TotalGames'] = $this->repoCollectionStats->userTotalGames($userId);
        $bindings['TotalHours'] = $this->repoCollectionStats->userTotalHours($userId);

        return view('user.collection.index', $bindings);
    }

    public function categoryBreakdown()
    {
        $pageTitle = 'Category breakdown';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->collectionSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $bindings['CategoryBreakdown'] = $this->dbUserGamesCollection->getCategoryBreakdown($userId);

        return view('user.collection.category-breakdown', $bindings);
    }

    public function topRatedByCategory($categoryId)
    {
        $category = $this->repoCategory->find($categoryId);
        if (!$category) abort(404);

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        $pageTitle = 'Category breakdown: '.$category->name;
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->collectionSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $bindings['Category'] = $category;
        $bindings['RankedGameList'] = $this->dbCategory->getRankedByCategory($category->id);
        $bindings['OwnedGamedIdList'] = $this->getServiceUserGamesCollection()->getGameIdsByUser($userId);

        return view('user.collection.top-rated-by-category', $bindings);
    }

    public function showList($listOption)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;
        $tableSort = '[4, "desc"]';

        switch ($listOption) {
            case UserGamesCollectionPlayStatus::PLAY_STATUS_NOT_STARTED:
                $pageTitle = 'Not started';
                $collectionList = $this->repoUserGamesCollection->byUserNotStarted($userId);
                break;
            case UserGamesCollectionPlayStatus::PLAY_STATUS_PAUSED:
                $pageTitle = 'Paused';
                $collectionList = $this->repoUserGamesCollection->byUserPaused($userId);
                break;
            case 'active':
                $pageTitle = 'Active';
                $collectionNowPlaying = $this->repoUserGamesCollection->byUserNowPlaying($userId);
                $collectionReplaying = $this->repoUserGamesCollection->byUserReplaying($userId);
                $collectionList = $collectionNowPlaying->merge($collectionReplaying);
                break;
            case UserGamesCollectionPlayStatus::PLAY_STATUS_COMPLETED:
                $pageTitle = 'Completed';
                $collectionList = $this->repoUserGamesCollection->byUserCompleted($userId);
                break;
            case UserGamesCollectionPlayStatus::PLAY_STATUS_ABANDONED:
                $pageTitle = 'Abandoned';
                $collectionList = $this->repoUserGamesCollection->byUserAbandoned($userId);
                break;
            case UserGamesCollectionPlayStatus::PLAY_STATUS_ENDLESS:
                $pageTitle = 'Endless';
                $collectionList = $this->repoUserGamesCollection->byUserEndless($userId);
                break;
            case 'recently-added':
                $pageTitle = 'All games in your collection';
                $collectionList = $this->repoUserGamesCollection->byUser($userId);
                break;
            default:
                abort(404);
        }

        $breadcrumbs = resolve('View/Breadcrumbs/Member')->collectionSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $bindings['CollectionList'] = $collectionList;
        $bindings['UserId'] = $userId;

        $quickReviewGameIdList = $this->getServiceQuickReview()->getAllByUserGameIdList($userId);
        $bindings['QuickReviewGameIdList'] = $quickReviewGameIdList;

        return view('user.collection.list', $bindings);
    }

    public function add()
    {
        $pageTitle = 'Add game to collection';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->collectionSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $serviceCollection = $this->getServiceUserGamesCollection();

        $request = request();

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

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

            $userGamesCollection = $serviceCollection->create(
                $userId, $request->game_id, $request->owned_from, $request->owned_type,
                $request->hours_played, $request->play_status
            );

            // Trigger event
            event(new GameCollectionAdded($userGamesCollection));

            return redirect(route('user.collection.list', ['listOption' => 'recently-added']));

        }

        $bindings['FormMode'] = 'add';

        $bindings['PlayStatusList'] = $this->ugcPlayStatus->generateAll();

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
        $pageTitle = 'Edit games collection item';
        $breadcrumbs = resolve('View/Breadcrumbs/Member')->collectionSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Member')->setBreadcrumbs($breadcrumbs)->generateMember($pageTitle);

        $serviceCollection = $this->getServiceUserGamesCollection();

        $request = request();

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

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

        $bindings['PlayStatusList'] = $this->ugcPlayStatus->generateAll();

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

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        if ($collectionItem->user_id != $userId) {
            return response()->json(['error' => 'Collection item belongs to another user'], 400);
        }

        // Delete from collection
        $serviceCollection->delete($collectionItemId);

        // Trigger event
        event(new GameCollectionRemoved($collectionItem));

        $data = array(
            'status' => 'OK'
        );
        return response()->json($data, 200);
    }
}
