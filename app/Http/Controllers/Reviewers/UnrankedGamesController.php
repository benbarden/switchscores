<?php

namespace App\Http\Controllers\Reviewers;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

use App\Domain\Unranked\Repository as UnrankedRepository;

class UnrankedGamesController extends Controller
{
    use SwitchServices;

    public function __construct(
        private UnrankedRepository $repoUnranked
    )
    {
    }

    public function landing()
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->partner_id;
        if ($partnerId == 0) {
            abort(403);
        }

        $bindings = [];

        $bindings['TopTitle'] = 'Unranked games';
        $bindings['PageTitle'] = 'Unranked games';

        return view('reviewers.unranked-games.landing', $bindings);
    }

    public function showList($mode, $filter)
    {
        $currentUser = resolve('User/Repository')->currentUser();
        $partnerId = $currentUser->partner_id;
        if ($partnerId == 0) {
            abort(403);
        }

        $allowedYears = resolve('Domain\GameCalendar\AllowedDates')->releaseYears();

        $serviceReviewLink = $this->getServiceReviewLink();

        $gameIdsReviewedBySite = $serviceReviewLink->getAllGameIdsReviewedBySite($partnerId);
        $totalGameIdsReviewedBySite = $serviceReviewLink->countAllGameIdsReviewedBySite($partnerId);

        $bindings = [];

        switch ($mode) {

            case 'by-count':
                if (!in_array($filter, ['0', '1', '2'])) abort(404);
                //$gamesList = $serviceGameReleaseDate->getUnrankedByReviewCount($filter, $gameIdsReviewedBySite);
                $gamesList = $this->repoUnranked->getByReviewCount($filter, $gameIdsReviewedBySite);
                if ($filter == 0) {
                    $tableSort = "[3, 'asc']";
                } else {
                    $tableSort = "[5, 'desc']";
                }
                $bindings['FilterOnLoad'] = 'by-count-'.$filter;
                break;

            case 'by-year':
                if (!in_array($filter, $allowedYears)) abort(404);
                //$gamesList = $serviceGameReleaseDate->getUnrankedByYear($filter, $gameIdsReviewedBySite);
                $gamesList = $this->repoUnranked->getByYear($filter, $gameIdsReviewedBySite);
                $tableSort = "[3, 'asc']";
                $bindings['FilterOnLoad'] = 'by-year-'.$filter;
                break;

            default:
                abort(404);

        }

        $bindings['GamesList'] = $gamesList;
        $bindings['GamesTableSort'] = $tableSort;

        $bindings['GamesReviewedCount'] = $totalGameIdsReviewedBySite;
        $bindings['AllowedYears'] = $allowedYears;

        $bindings['PageMode'] = $mode;
        $bindings['PageFilter'] = $filter;

        $bindings['TopTitle'] = 'Unranked games';
        $bindings['PageTitle'] = 'Unranked games';

        return view('reviewers.unranked-games.list', $bindings);
    }
}
