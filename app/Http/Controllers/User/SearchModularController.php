<?php

namespace App\Http\Controllers\User;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;
use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;
use App\Domain\Game\Repository as GameRepository;

use App\Traits\MemberView;
use App\Traits\SwitchServices;

class SearchModularController extends Controller
{
    use SwitchServices;
    use MemberView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesFindGame = [
        'search_keywords' => 'required|min:3',
    ];

    public function __construct(
        private UserGamesCollectionRepository $repoUserGamesCollection,
        private FeaturedGameRepository $repoFeaturedGame,
        private GameRepository $repoGame
    )
    {
    }

    public function findGame($searchMode)
    {
        $allowedSearchModes = [
            'add-quick-review',
            'add-featured-game',
            'add-collection-item'
        ];

        if (!in_array($searchMode, $allowedSearchModes)) abort(404);

        $bindings = $this->getBindingsDashboardGenericSubpage('Find game');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesFindGame);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->repoGame->searchByTitle($keywords);
            }

        }

        $bindings['SearchMode'] = $searchMode;

        $currentUser = resolve('User/Repository')->currentUser();
        $userId = $currentUser->id;

        switch ($searchMode) {
            case 'add-quick-review':
                $bindings['ReviewedGameIdList'] = $this->getServiceQuickReview()->getAllByUserGameIdList($userId);
                break;
            case 'add-featured-game':
                $bindings['FeaturedGameIdList'] = $this->repoFeaturedGame->getAllGameIds();
                break;
            case 'add-collection-item':
                $bindings['CollectionGameIdList'] = $this->repoUserGamesCollection->byUserGameIds($userId);
                break;
        }

        return view('user.search-modular.game-search', $bindings);
    }
}