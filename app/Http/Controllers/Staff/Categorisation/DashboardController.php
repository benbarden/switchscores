<?php

namespace App\Http\Controllers\Staff\Categorisation;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\DB;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;

use App\Domain\GameStats\Repository as GameStatsRepository;
use App\Domain\TagCategory\Repository as TagCategoryRepository;

class DashboardController extends Controller
{
    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private GameStatsRepository $repoGameStats,
        private TagCategoryRepository $repoTagCategory,
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

        // Tag category progress (excluding low quality and de-listed games)
        $totalGames = DB::table('games')
            ->where('is_low_quality', 0)
            ->where(function ($query) {
                $query->where('format_digital', '<>', 'De-listed')
                      ->orWhereNull('format_digital');
            })
            ->count();

        $tagCategories = $this->repoTagCategory->getAll();

        $tagCategoryProgress = [];
        foreach ($tagCategories as $tagCategory) {
            // Count games that have at least one tag from this category
            $gamesWithTag = DB::table('games')
                ->join('game_tags', 'games.id', '=', 'game_tags.game_id')
                ->join('tags', 'game_tags.tag_id', '=', 'tags.id')
                ->where('tags.tag_category_id', $tagCategory->id)
                ->where('games.is_low_quality', 0)
                ->where(function ($query) {
                    $query->where('games.format_digital', '<>', 'De-listed')
                          ->orWhereNull('games.format_digital');
                })
                ->distinct('games.id')
                ->count('games.id');

            $gamesWithoutTag = $totalGames - $gamesWithTag;
            $percentage = $totalGames > 0 ? round(($gamesWithTag / $totalGames) * 100, 1) : 0;

            $tagCategoryProgress[] = [
                'id' => $tagCategory->id,
                'name' => $tagCategory->name,
                'games_with_tag' => $gamesWithTag,
                'games_without_tag' => $gamesWithoutTag,
                'percentage' => $percentage,
            ];
        }

        $bindings['TagCategoryProgress'] = $tagCategoryProgress;
        $bindings['TotalGames'] = $totalGames;

        return view('staff.categorisation.dashboard', $bindings);
    }
}
