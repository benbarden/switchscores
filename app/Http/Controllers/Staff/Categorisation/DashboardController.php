<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\GameLists\MissingCategory as GameListMissingCategoryRepository;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameStatsRepository $repoGameStats,
        private GameListMissingCategoryRepository $repoGameListMissingCategory,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Categorisation dashboard';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::categorisationDashboard())->bindings;

        // Action lists: Category
        $bindings['NoCategoryCount'] = $this->repoGameStats->totalNoCategoryAll();
        $bindings['NoCategoryWithCollectionCount'] = $this->repoGameStats->totalNoCategoryWithCollectionId();
        $bindings['NoCategoryWithReviewsCount'] = $this->repoGameStats->totalNoCategoryWithReviews();

        // Bulk edit stats
        $missingCategorySimulation = $this->repoGameListMissingCategory->simulation();
        $bindings['BulkEditMissingCategorySimCount'] = count($missingCategorySimulation);
        $missingCategorySurvival = $this->repoGameListMissingCategory->survival();
        $bindings['BulkEditMissingCategorySurvivalCount'] = count($missingCategorySurvival);
        $missingCategoryQuiz = $this->repoGameListMissingCategory->quiz();
        $bindings['BulkEditMissingCategoryQuizCount'] = count($missingCategoryQuiz);
        $missingCategorySpotTheDifference = $this->repoGameListMissingCategory->spotTheDifference();
        $bindings['BulkEditMissingCategorySpotTheDifferenceCount'] = count($missingCategorySpotTheDifference);
        $missingCategoryPuzzle = $this->repoGameListMissingCategory->puzzle();
        $bindings['BulkEditMissingCategoryPuzzleCount'] = count($missingCategoryPuzzle);
        $missingCategorySportsRacing = $this->repoGameListMissingCategory->sportsAndRacing();
        $bindings['BulkEditMissingCategorySportsRacingCount'] = count($missingCategorySportsRacing);
        $missingCategoryHidden = $this->repoGameListMissingCategory->hidden();
        $bindings['BulkEditMissingCategoryHiddenCount'] = count($missingCategoryHidden);
        $missingCategoryEscape = $this->repoGameListMissingCategory->escape();
        $bindings['BulkEditMissingCategoryEscapeCount'] = count($missingCategoryEscape);
        $missingCategoryHentaiGirls = $this->repoGameListMissingCategory->hentaiGirls();
        $bindings['BulkEditMissingCategoryHentaiGirlsCount'] = count($missingCategoryHentaiGirls);
        $missingCategoryDroneFlyingTour = $this->repoGameListMissingCategory->droneFlyingTour();
        $bindings['BulkEditMissingCategoryDroneFlyingTourCount'] = count($missingCategoryDroneFlyingTour);

        return view('staff.categorisation.dashboard', $bindings);
    }
}
