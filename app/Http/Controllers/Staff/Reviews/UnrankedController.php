<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Domain\Unranked\Repository as UnrankedRepository;

use App\Traits\SwitchServices;

class UnrankedController extends Controller
{
    use SwitchServices;

    private $repoUnranked;

    public function __construct(
        UnrankedRepository $repoUnranked
    )
    {
        $this->repoUnranked = $repoUnranked;
    }

    public function reviewCountLanding()
    {
        $pageTitle = 'Unranked games: By review count';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Unranked breakdown
        $bindings['UnrankedReviews2List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(2, 15);
        $bindings['UnrankedReviews1List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(1, 15);
        $bindings['UnrankedReviews0List'] = $this->getServiceTopRated()->getUnrankedListByReviewCount(0, 15);

        return view('staff.reviews.unranked.reviewCountLanding', $bindings);
    }

    public function reviewCountList($reviewCount)
    {
        $pageTitle = 'Unranked games: '.$reviewCount.' review(s)';
        $tableSort = "[ 4, 'desc'], [ 2, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsUnrankedByReviewCountSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoUnranked->getByReviewCount($reviewCount);

        return view('staff.games.list.unranked-games', $bindings);
    }

    public function releaseYearLanding()
    {
        $pageTitle = 'Unranked games: By release year';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        // Unranked breakdown
        $bindings['UnrankedYear2020List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2020, 15);
        $bindings['UnrankedYear2019List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2019, 15);
        $bindings['UnrankedYear2018List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2018, 15);
        $bindings['UnrankedYear2017List'] = $this->getServiceTopRated()->getUnrankedListByReleaseYear(2017, 15);

        return view('staff.reviews.unranked.releaseYearLanding', $bindings);
    }

    public function releaseYearList($releaseYear)
    {
        $pageTitle = 'Unranked games: released '.$releaseYear;
        $tableSort = "[ 4, 'desc'], [ 2, 'desc']";
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->reviewsUnrankedByReleaseYearSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')
            ->setTableSort($tableSort)->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bindings['GameList'] = $this->repoUnranked->getByYear($releaseYear);

        return view('staff.games.list.unranked-games', $bindings);
    }
}
