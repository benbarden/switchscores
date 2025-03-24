<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;

use App\Domain\GameSeries\Repository as GameSeriesRepository;
use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\Tag\Repository as TagRepository;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\GameLists\MissingCategory as GameListMissingCategoryRepository;

use App\Models\Category;
use App\Models\GameSeries;
use App\Models\Tag;

use App\Traits\SwitchServices;

class DashboardController extends Controller
{
    use SwitchServices;

    public function __construct(
        private GameStatsRepository $repoGameStats,
        private GameSeriesRepository $repoGameSeries,
        private TagRepository $repoTag,
        private CategoryRepository $repoCategory,
        private GameListMissingCategoryRepository $repoGameListMissingCategory,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'Categorisation dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

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

        return view('staff.categorisation.dashboard', $bindings);
    }
}
