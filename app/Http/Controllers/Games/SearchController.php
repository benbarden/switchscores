<?php

namespace App\Http\Controllers\Games;

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
        $bindings = [];

        $pageTitle = 'Search';

        $request = request();

        if ($request->isMethod('post')) {

            $this->validate($request, $this->validationRules);

            $keywords = request()->search_keywords;

            if ($keywords) {
                $bindings['SearchKeywords'] = $keywords;
                $bindings['SearchResults'] = $this->getServiceGame()->searchByTitle($keywords);
            }

        }

        $bindings['TopTitle'] = $pageTitle;
        $bindings['PageTitle'] = $pageTitle;

        $bindings['jsInitialSort'] = "[0, 'desc']";

        return view('games.search.show', $bindings);
    }
}
