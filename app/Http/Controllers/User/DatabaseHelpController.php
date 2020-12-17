<?php

namespace App\Http\Controllers\User;

use App\Construction\DbEdit\GameBuilder;
use App\Construction\DbEdit\GameDirector;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\DbEditGame;
use App\Services\Migrations\Category as MigrationsCategory;

use App\Traits\AuthUser;
use App\Traits\SwitchServices;

class DatabaseHelpController extends Controller
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
        $bindings = [];

        $serviceMigrationsCategory = new MigrationsCategory();
        $bindings['NoCategoryCount'] = $serviceMigrationsCategory->countGamesWithNoCategory();

        $bindings['TopTitle'] = 'User - Database help - Index';
        $bindings['PageTitle'] = 'Database help';

        return view('user.database-help.index', $bindings);
    }

    public function gamesWithoutCategories()
    {
        $allowedYears = $this->getServiceGameCalendar()->getAllowedYears();
        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings = [];

        $bindings['AllowedYears'] = $allowedYears;

        foreach ($allowedYears as $year) {
            $bindings['NoCategoryCount'.$year] = $serviceMigrationsCategory->countGamesWithNoCategory($year);
        }

        $bindings['TopTitle'] = 'User - Database help - Games without categories';
        $bindings['PageTitle'] = 'Games without categories';

        return view('user.database-help.games-without-categories.index', $bindings);
    }

    public function gamesWithoutCategoriesByYear($year)
    {
        $serviceMigrationsCategory = new MigrationsCategory();

        $bindings = [];

        $bindings['GameList'] = $serviceMigrationsCategory->getGamesWithNoCategory($year);

        $bindings['PendingCategoryEditsGameIdList'] = $this->getServiceDbEditGame()->getPendingCategoryEditsGameIdList();

        $bindings['TopTitle'] = 'User - Database help - Games without categories';
        $bindings['PageTitle'] = 'Games without categories';

        return view('user.database-help.games-without-categories.list', $bindings);
    }

    public function submitGameCategorySuggestion($gameId)
    {
        $request = request();

        $game = $this->getServiceGame()->find($gameId);
        if (!$game) abort(404);

        $bindings = [];

        if ($request->isMethod('post')) {

            $dbEditDirector = new GameDirector();
            $dbEditBuilder = new GameBuilder();

            $dbEditDirector->setBuilder($dbEditBuilder);

            $params = [
                'user_id' => $this->getAuthId(),
                'game_id' => $gameId,
                'data_to_update' => DbEditGame::DATA_CATEGORY,
                'current_data' => $game->category_id,
                'new_data' => $request->category_id,
            ];
            $dbEditDirector->buildNew($params);
            $dbEditGame = $dbEditBuilder->getDbEditGame();
            $dbEditGame->save();

            return redirect(route('user.database-help.games-without-categories'));

        }

        $bindings['TopTitle'] = 'User - Database help - Submit category change suggestion';
        $bindings['PageTitle'] = 'Submit category change suggestion';
        $bindings['FormMode'] = 'add';

        $bindings['GameId'] = $gameId;
        $bindings['GameData'] = $game;
        $bindings['CategoryList'] = $this->getServiceCategory()->getAllWithoutParents();
        $bindings['DataSourceNintendoCoUk'] = $this->getServiceDataSourceParsed()->getSourceNintendoCoUkForGame($gameId);

        return view('user.database-help.games-without-categories.form', $bindings);
    }

    ////////////////////////////////////////////////

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

        $urlGameId = $request->gameId;
        if ($urlGameId) {
            $bindings['UrlGameId'] = $urlGameId;
        }

        return view('user.collection.add', $bindings);
    }

    public function edit($itemId)
    {
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

        return view('user.collection.edit', $bindings);
    }
}
