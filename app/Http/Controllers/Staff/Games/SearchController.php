<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Game\Repository as GameRepository;

class SearchController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'search_keywords' => 'required|min:3',
    ];

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameRepository $repoGame
    )
    {

    }

    public function show()
    {
        $pageTitle = 'Search';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::gamesSubpage($pageTitle))->bindings;

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->repoGame->searchByTitle($keywords);
            }

        }

        return view('staff.games.search.show', $bindings);
    }
}
