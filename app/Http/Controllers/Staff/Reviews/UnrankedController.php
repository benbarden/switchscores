<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Traits\SwitchServices;
use App\Traits\StaffView;

class UnrankedController extends Controller
{
    use SwitchServices;
    use StaffView;

    public function reviewCountLanding()
    {
        $bindings = $this->getBindingsReviewsSubpage('Unranked games: By review count');

        // Unranked breakdown
        $bindings['UnrankedReviews2List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(2, 15);
        $bindings['UnrankedReviews1List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(1, 15);
        $bindings['UnrankedReviews0List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(0, 15);

        return view('staff.reviews.unranked.reviewCountLanding', $bindings);
    }

    public function reviewCountList($reviewCount)
    {
        $bindings = $this->getBindingsReviewsUnrankedByReviewCountSubpage('Unranked games: '.$reviewCount.' review(s)', "[ 4, 'desc'], [ 2, 'desc']");

        $bindings['GameList'] = $this->getServiceTopRated()->getUnrankedListByReviewCount($reviewCount);

        return view('staff.games.list.unranked-games', $bindings);
    }

    public function releaseYearLanding()
    {
        $bindings = $this->getBindingsReviewsSubpage('Unranked games: By release year');

        // Unranked breakdown
        $bindings['UnrankedYear2020List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2020, 15);
        $bindings['UnrankedYear2019List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2019, 15);
        $bindings['UnrankedYear2018List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2018, 15);
        $bindings['UnrankedYear2017List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2017, 15);

        return view('staff.reviews.unranked.releaseYearLanding', $bindings);
    }

    public function releaseYearList($releaseYear)
    {
        $bindings = $this->getBindingsReviewsUnrankedByReleaseYearSubpage('Unranked games: released '.$releaseYear, "[ 4, 'desc'], [ 2, 'desc']");

        $bindings['GameList'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear($releaseYear);

        return view('staff.games.list.unranked-games', $bindings);
    }
}
