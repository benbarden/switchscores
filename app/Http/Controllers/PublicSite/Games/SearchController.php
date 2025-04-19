<?php

namespace App\Http\Controllers\PublicSite\Games;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameCalendar\AllowedDates as GameCalendarAllowedDates;
use App\Domain\GameCollection\Repository as GameCollectionRepository;
use App\Domain\GameSearch\Builder as GameSearchBuilder;
use App\Domain\GameSeries\Repository as GameSeriesRepository;

class SearchController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        //'search_keywords' => 'required|min:3',
    ];

    public function __construct(
        private CategoryRepository $repoCategory,
        private GameSearchBuilder $searchBuilder,
        private GameSeriesRepository $repoGameSeries,
        private GameCollectionRepository $repoGameCollection,
        private GameCalendarAllowedDates $allowedDates
    )
    {
    }

    public function show()
    {
        $bindings = [];

        $pageTitle = 'Search';

        $request = request();

        if ($request->isMethod('get')) {

            $this->validate($request, $this->validationRules);

            $searchResults = $this->searchBuilder->build($request->all(), $bindings);
            $bindings['SearchResults'] = $searchResults;

        }

        // Search options
        $bindings['YearList'] = array_reverse($this->allowedDates->releaseYears(false));
        $bindings['CategoryList'] = $this->repoCategory->topLevelCategories();
        $bindings['GameSeriesList'] = $this->repoGameSeries->getAll();
        $bindings['CollectionList'] = $this->repoGameCollection->getAll();

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['jsInitialSort'] = "[0, 'desc']";

        return view('public.games.search.show', $bindings);
    }
}
