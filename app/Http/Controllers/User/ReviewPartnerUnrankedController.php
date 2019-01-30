<?php

namespace App\Http\Controllers\User;

use Illuminate\Routing\Controller as Controller;

use App\Services\ServiceContainer;
use Auth;

class ReviewPartnerUnrankedController extends Controller
{
    public function landing()
    {
        $userSiteId = Auth::user()->site_id;
        if ($userSiteId == 0) {
            abort(403);
        }

        $bindings = [];

        $bindings['TopTitle'] = 'Unranked games';
        $bindings['PageTitle'] = 'Unranked games';

        return view('user.review-partner.unrankedLanding', $bindings);
    }

    public function showList($mode, $filter)
    {
        $serviceContainer = \Request::get('serviceContainer');
        /* @var $serviceContainer ServiceContainer */

        $regionCode = \Request::get('regionCode');

        $userSiteId = Auth::user()->site_id;
        if ($userSiteId == 0) {
            abort(403);
        }

        $serviceGameReleaseDate = $serviceContainer->getGameReleaseDateService();

        $bindings = [];

        switch ($mode) {

            case 'by-count':
                if (!in_array($filter, ['0', '1', '2'])) abort(404);
                $gamesList = $serviceGameReleaseDate->getUnrankedByReviewCount($filter, $regionCode);
                $tableSort = "[1, 'asc']";
                break;

            case 'by-year':
                if (!in_array($filter, ['2017', '2018', '2019'])) abort(404);
                $gamesList = $serviceGameReleaseDate->getUnrankedByYear($filter, $regionCode);
                $tableSort = "[1, 'asc']";
                break;

            case 'by-list':
                if (!in_array($filter, ['aca-neogeo', 'arcade-archives', 'all-others'])) abort(404);
                $gamesList = $serviceGameReleaseDate->getUnrankedByList($filter, $regionCode);
                $tableSort = "[1, 'asc']";
                break;

            default:
                abort(404);

        }

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = $tableSort;

        $bindings['PageMode'] = $mode;
        $bindings['PageFilter'] = $filter;

        $bindings['TopTitle'] = 'Unranked games';
        $bindings['PageTitle'] = 'Unranked games';

        return view('user.review-partner.unrankedList', $bindings);
    }
}