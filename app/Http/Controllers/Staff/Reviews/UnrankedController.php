<?php

namespace App\Http\Controllers\Staff\Reviews;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\Unranked\Repository as UnrankedRepository;

class UnrankedController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private UnrankedRepository $repoUnranked
    )
    {
    }

    public function reviewCountLanding()
    {
        $pageTitle = 'Unranked games: By review count';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        // Unranked breakdown
        $bindings['UnrankedReviews2List'] = $this->repoUnranked->getByReviewCount(2, null, 15);
        $bindings['UnrankedReviews1List'] = $this->repoUnranked->getByReviewCount(1, null, 15);
        $bindings['UnrankedReviews0List'] = $this->repoUnranked->getByReviewCount(0, null, 15);

        return view('staff.reviews.unranked.reviewCountLanding', $bindings);
    }

    public function reviewCountList($reviewCount)
    {
        $pageTitle = 'Unranked games: '.$reviewCount.' review(s)';
        $tableSort = "[ 4, 'desc'], [ 2, 'desc']";
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsUnrankedByReviewCountSubpage($pageTitle), jsInitialSort: $tableSort)->bindings;

        $bindings['GameList'] = $this->repoUnranked->getByReviewCount($reviewCount);

        return view('staff.games.list.unranked-games', $bindings);
    }

    public function releaseYearLanding()
    {
        $pageTitle = 'Unranked games: By release year';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsSubpage($pageTitle))->bindings;

        // Unranked breakdown
        $bindings['UnrankedYear2025List'] = $this->repoUnranked->getByYear(2025, null, 15,);
        $bindings['UnrankedYear2024List'] = $this->repoUnranked->getByYear(2024, null, 15,);
        $bindings['UnrankedYear2023List'] = $this->repoUnranked->getByYear(2023, null, 15,);

        return view('staff.reviews.unranked.releaseYearLanding', $bindings);
    }

    public function releaseYearList($releaseYear)
    {
        $pageTitle = 'Unranked games: released '.$releaseYear;
        $tableSort = "[ 4, 'desc'], [ 2, 'desc']";
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::reviewsUnrankedByReleaseYearSubpage($pageTitle), jsInitialSort: $tableSort)->bindings;

        $bindings['GameList'] = $this->repoUnranked->getByYear($releaseYear);

        return view('staff.games.list.unranked-games', $bindings);
    }
}
