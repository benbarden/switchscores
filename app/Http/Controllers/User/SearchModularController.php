<?php


namespace App\Http\Controllers\User;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;

use App\Traits\MemberView;
use App\Traits\AuthUser;
use App\Traits\SwitchServices;

class SearchModularController extends Controller
{
    use SwitchServices;
    use AuthUser;
    use MemberView;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRulesFindGame = [
        'search_keywords' => 'required|min:3',
    ];

    protected $repoFeaturedGame;

    public function __construct(FeaturedGameRepository $featuredGame)
    {
        $this->repoFeaturedGame = $featuredGame;
    }

    public function findGame($searchMode)
    {
        $allowedSearchModes = [
            'add-quick-review',
            'add-featured-game',
        ];

        if (!in_array($searchMode, $allowedSearchModes)) abort(404);

        $bindings = $this->getBindingsDashboardGenericSubpage('Find game');

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRulesFindGame);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->getServiceGame()->searchByTitle($keywords);
            }

        }

        $bindings['SearchMode'] = $searchMode;

        switch ($searchMode) {
            case 'add-quick-review':
                $bindings['ReviewedGameIdList'] = $this->getServiceQuickReview()->getAllByUserGameIdList($this->getAuthId());
                break;
            case 'add-featured-game':
                $bindings['FeaturedGameIdList'] = $this->repoFeaturedGame->getAllGameIds();
                break;
        }

        return view('user.search-modular.game-search', $bindings);
    }
}