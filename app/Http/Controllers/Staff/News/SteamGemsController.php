<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use App\Domain\View\Breadcrumbs\StaffBreadcrumbs;
use App\Domain\View\PageBuilders\StaffPageBuilder;
use App\Domain\Category\Repository as CategoryRepository;
use App\Domain\News\Repository as RepoNews;
use App\Domain\Game\Repository as RepoGame;
use App\Enums\FeatureQueueBucket;
use App\Enums\SteamStatus;
use App\Models\Category;
use App\Models\FeatureQueue;
use App\Models\Game;
use App\Models\News;
use Illuminate\Support\Str;

class SteamGemsController extends Controller
{
    private const DRAFT_THRESHOLD = 10;

    public function __construct(
        private StaffPageBuilder $pageBuilder,
        private CategoryRepository $repoCategory,
        private RepoNews $repoNews,
        private RepoGame $repoGame,
    )
    {
    }

    public function index()
    {
        $pageTitle = 'Steam gems';
        $bindings = $this->pageBuilder->build($pageTitle, StaffBreadcrumbs::newsSubpage($pageTitle))->bindings;

        $bucket = FeatureQueueBucket::UNRANKED_STEAM_GEM->value;

        $topLevel = $this->repoCategory->topLevelCategories();

        // Build a flat ordered list: parent, then its children, then next parent etc.
        $flatCategories = [];
        foreach ($topLevel as $parent) {
            $flatCategories[] = ['category' => $parent, 'is_child' => false];
            foreach ($parent->children as $child) {
                $flatCategories[] = ['category' => $child, 'is_child' => true];
            }
        }

        // First pass: calculate counts for all categories
        $allRows = [];
        foreach ($flatCategories as $item) {
            $cat = $item['category'];

            $potential = Game::where('steam_status', SteamStatus::LINKED->value)
                ->where('is_low_quality', 0)
                ->where('category_id', $cat->id)
                ->whereNull('game_rank')
                ->active()
                ->where(function ($q) {
                    $q->whereNull('review_count')->orWhere('review_count', '<', 3);
                })
                ->join('steam_review_data', 'steam_review_data.game_id', '=', 'games.id')
                ->where('steam_review_data.review_score', '>=', 7)
                ->whereRaw('games.id = (SELECT MIN(g2.id) FROM games g2 WHERE g2.steam_id = games.steam_id AND g2.is_low_quality = 0 AND g2.game_status = "active")')
                ->whereNotIn('games.id', function ($q) use ($bucket, $cat) {
                    $q->select('game_id')
                      ->from('feature_queue')
                      ->where('bucket', $bucket)
                      ->where('category_id', $cat->id);
                })
                ->count();

            $inQueue = FeatureQueue::where('bucket', $bucket)
                ->where('category_id', $cat->id)
                ->whereNull('used_at')
                ->count();

            $used = FeatureQueue::where('bucket', $bucket)
                ->where('category_id', $cat->id)
                ->whereNotNull('used_at')
                ->count();

            $allRows[] = [
                'category'     => $cat,
                'is_child'     => $item['is_child'],
                'parent_id'    => $item['is_child'] ? $cat->parent_id : null,
                'potential'    => $potential,
                'in_queue'     => $inQueue,
                'used'         => $used,
                'can_generate' => $inQueue >= self::DRAFT_THRESHOLD,
            ];
        }

        // Find which parent IDs have at least one child with records
        $parentsWithActiveChildren = [];
        foreach ($allRows as $row) {
            if ($row['is_child'] && ($row['potential'] > 0 || $row['in_queue'] > 0 || $row['used'] > 0)) {
                $parentsWithActiveChildren[$row['parent_id']] = true;
            }
        }

        // Second pass: filter out empty rows, but keep parents that have active children
        $rows = [];
        foreach ($allRows as $row) {
            $hasRecords = $row['potential'] > 0 || $row['in_queue'] > 0 || $row['used'] > 0;
            $isKeptParent = !$row['is_child'] && isset($parentsWithActiveChildren[$row['category']->id]);

            if (!$hasRecords && !$isKeptParent) {
                continue;
            }

            $rows[] = $row;
        }

        $bindings['Rows'] = $rows;
        $bindings['DraftThreshold'] = self::DRAFT_THRESHOLD;

        return view('staff.news.steam-gems.index', $bindings);
    }

    public function enqueue(Request $request, int $categoryId)
    {
        $category = Category::findOrFail($categoryId);

        Artisan::call('features:enqueue', [
            '--bucket'      => FeatureQueueBucket::UNRANKED_STEAM_GEM->value,
            '--category-id' => $categoryId,
            '--refresh'     => true,
        ]);

        return redirect()
            ->route('staff.news.steam-gems.index')
            ->with('success', "Enqueued Steam gems for \"{$category->name}\".");
    }

    public function generate(Request $request, int $categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $bucket   = FeatureQueueBucket::UNRANKED_STEAM_GEM->value;
        $limit    = self::DRAFT_THRESHOLD;

        $picks = FeatureQueue::with(['game', 'game.steamReviewData'])
            ->where('bucket', $bucket)
            ->where('category_id', $categoryId)
            ->whereNull('used_at')
            ->orderByDesc('priority')
            ->orderBy('queued_at')
            ->limit($limit)
            ->get();

        if ($picks->count() < self::DRAFT_THRESHOLD) {
            return back()->with('error', "Not enough games in queue (need " . self::DRAFT_THRESHOLD . ", have {$picks->count()}).");
        }

        $firstGameId = null;
        $gameHtml    = '';

        foreach ($picks as $pick) {
            $game = $pick->game;
            if ($firstGameId === null) $firstGameId = $game->id;

            $steamSentiment = '';
            if ($game->steamReviewData) {
                $steamSentiment = '<p><em>Steam: ' . e($game->steamReviewData->review_score_desc)
                    . ' — ' . number_format($game->steamReviewData->total_reviews) . ' reviews</em></p>';
            }

            $gameHtml .= '<h3>' . e($game->title) . '</h3>' . "\n";
            $gameHtml .= $steamSentiment;
            $gameHtml .= '[gameheader ids="' . $game->id . '"]' . "\n";
            $gameHtml .= '[gameblurb ids="' . $game->id . '"]' . "\n";
        }

        $month       = now()->format('F Y');
        $categorySlug = Str::slug($category->name);
        $postTitle   = "{$category->name} Switch games loved on Steam — {$month}";
        $postSlug    = "{$categorySlug}-switch-games-loved-on-steam-" . now()->format('Y-m');
        $url         = '/news/' . now()->format('Y-m-d') . '/' . $postSlug;

        $existingPost = $this->repoNews->getByUrl($url);
        if ($existingPost) {
            return redirect()->route('staff.news.edit', $existingPost->id);
        }

        $intro = "These {$category->name} Switch games haven't been reviewed much on Switch Scores yet, "
            . "but Steam players love them. If you've played any of these, consider adding your review.";

        $contentHtml = "<p>{$intro}</p>\n{$gameHtml}";

        $post = $this->repoNews->create($postTitle, 8, $url, $contentHtml, $firstGameId);

        DB::table('news_post_games')->insert($picks->map(fn($row) => [
            'news_post_id'     => $post->id,
            'game_id'          => $row->game_id,
            'bucket'           => $bucket,
            'feature_queue_id' => $row->id,
            'created_at'       => now(),
            'updated_at'       => now(),
        ])->all());

        FeatureQueue::whereIn('id', $picks->pluck('id'))->update(['used_at' => now()]);

        return redirect()
            ->route('staff.news.edit', $post->id)
            ->with('success', "Draft created with {$picks->count()} games.");
    }
}
