<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;

class UnrankedController extends Controller
{
    use SwitchServices;

    private function getBindings($functionName, $pageTitle, $tableSort = '')
    {
        switch ($functionName) {
            case 'reviewCountList':
                $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsUnrankedByReviewCountSubPage($pageTitle);
                break;
            case 'releaseYearList':
                $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsUnrankedByReleaseYearSubPage($pageTitle);
                break;
            default:
                $breadcrumbs = $this->getServiceViewHelperStaffBreadcrumbs()->makeReviewsSubPage($pageTitle);
        }

        $bindings = $this->getServiceViewHelperBindings()
            ->setPageTitle($pageTitle)
            ->setTopTitlePrefix('Reviews')
            ->setBreadcrumbs($breadcrumbs);

        if ($tableSort) {
            $bindings = $bindings->setDatatablesSort($tableSort);
        }

        return $bindings->getBindings();
    }

    public function reviewCountLanding()
    {
        $bindings = $this->getBindings(__FUNCTION__, 'Unranked games: By review count');

        // Unranked breakdown
        $bindings['UnrankedReviews2List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(2, 15);
        $bindings['UnrankedReviews1List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(1, 15);
        $bindings['UnrankedReviews0List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(0, 15);

        return view('staff.reviews.unranked.reviewCountLanding', $bindings);
    }

    public function reviewCountList($reviewCount)
    {
        $bindings = $this->getBindings(__FUNCTION__, 'Unranked games: '.$reviewCount.' review(s)', "[ 4, 'desc'], [ 2, 'desc']");

        $bindings['GameList'] = $this->getServiceTopRated()->getUnrankedListByReviewCount($reviewCount);

        return view('staff.games.list.unranked-games', $bindings);
    }

    public function releaseYearLanding()
    {
        $bindings = $this->getBindings(__FUNCTION__, 'Unranked games: By release year');

        // Unranked breakdown
        $bindings['UnrankedYear2020List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2020, 15);
        $bindings['UnrankedYear2019List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2019, 15);
        $bindings['UnrankedYear2018List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2018, 15);
        $bindings['UnrankedYear2017List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2017, 15);

        return view('staff.reviews.unranked.releaseYearLanding', $bindings);
    }

    public function releaseYearList($releaseYear)
    {
        $bindings = $this->getBindings(__FUNCTION__, 'Unranked games: released '.$releaseYear, "[ 4, 'desc'], [ 2, 'desc']");

        $bindings['GameList'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear($releaseYear);

        return view('staff.games.list.unranked-games', $bindings);
    }
}
