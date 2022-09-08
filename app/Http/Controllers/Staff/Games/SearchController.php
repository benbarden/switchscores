<?php

namespace App\Http\Controllers\Staff\Games;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class SearchController extends Controller
{
    use SwitchServices;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @var array
     */
    private $validationRules = [
        'search_keywords' => 'required|min:3',
    ];

    public function show()
    {
        $pageTitle = 'Search';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->gamesSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->getServiceGame()->searchByTitle($keywords);
            }

        }

        return view('staff.games.search.show', $bindings);
    }
}
