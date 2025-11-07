<?php

namespace App\Http\Controllers\Staff\News;

use Illuminate\Routing\Controller as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FeatureQueue;
use App\Models\News;
use App\Enums\FeatureQueueBucket;
use App\Domain\Game\AutoDescription;

class DashboardController extends Controller
{
    public function __construct(
        private AutoDescription $autoDescription,
    )
    {
    }

    public function show()
    {
        $pageTitle = 'News dashboard';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->topLevelPage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        return view('staff.news.dashboard', $bindings);
    }

    public function cleanupBucket($bucket)
    {
        if ($bucket == 'has-2-reviews') {
            DB::statement("
                DELETE fq FROM feature_queue fq
                JOIN games g ON g.id = fq.game_id
                WHERE fq.bucket = ?
                AND fq.used_at IS NULL
                AND (
                g.review_count <> 2
                OR g.is_low_quality = 1
                OR g.format_digital = 'De-listed'
                )
            ", [$bucket]);
        }
    }

    public function bucket(Request $request, string $bucket)
    {
        $pageTitle = 'News buckets';
        $breadcrumbs = resolve('View/Breadcrumbs/Staff')->newsSubpage($pageTitle);
        $bindings = resolve('View/Bindings/Staff')->setBreadcrumbs($breadcrumbs)->generateStaff($pageTitle);

        $bucketEnum = FeatureQueueBucket::tryFromSlug($bucket);
        abort_unless($bucketEnum != null, 404);

        $this->cleanupBucket($bucket);

        $ready = FeatureQueue::with(['game'])
            ->where('bucket', $bucket)
            ->whereNull('used_at')
            ->orderByDesc('priority')
            ->orderBy('queued_at')
            ->paginate(25, ['*'], 'ready_page');

        $used = FeatureQueue::with(['game'])
            ->where('bucket', $bucket)
            ->whereNotNull('used_at')
            ->orderByDesc('used_at')
            ->paginate(25, ['*'], 'used_page');

        $bindings = array_merge($bindings, [
            'bucket' => $bucket,
            'bucket_label' => $bucketEnum->label(),
            'ready'  => $ready,
            'used'   => $used,
        ]);

        return view('staff.news.bucket', $bindings);
    }

    public function enqueue(Request $request, string $bucket)
    {
        $bucketEnum = FeatureQueueBucket::tryFromSlug($bucket);
        abort_unless($bucketEnum != null, 404);

        $this->cleanupBucket($bucket);

        // For now only "almost_ranked". Later we’ll branch to other enqueue commands.
        if ($bucket !== 'has-2-reviews') {
            return back()->with('error', 'Enqueue not implemented for this bucket yet.');
        }

        // Call the command you created (or inline the SQL if you prefer)
        \Artisan::call('features:enqueue', [
            '--bucket' => $request->input('bucket', 'has-2-reviews'),
            '--min-score' => $request->input('min_score', 7.5),
            '--cooldown-days' => $request->input('cooldown_days', 120),
        ]);

        return back()->with('success', 'Enqueued has-2-reviews candidates.');
    }

    public function generateDraft(string $bucket)
    {
        $bucketEnum = FeatureQueueBucket::tryFromSlug($bucket);
        abort_unless($bucketEnum != null, 404);

        $limit = 6; // how many games per post

        // 1. Pick top N ready items
        $picks = FeatureQueue::with('game')
            ->where('bucket', $bucket)
            ->whereNull('used_at')
            ->orderByDesc('priority')
            ->orderBy('queued_at')
            ->limit($limit)
            ->get();

        if ($picks->isEmpty()) {
            return back()->with('error', 'No ready candidates found.');
        }

        // 2. Build shortcode string (adjust if your shortcode differs)
        //$ids = $picks->pluck('game_id')->implode(',');

        // Build shortcode HTML
        $firstGameId = null;
        $gameHtml = '';
        foreach ($picks as $pickItem) {
            $gameId = $pickItem->game->id;
            if ($firstGameId == null) $firstGameId = $gameId;
            $gameHtml .= '<h3>'.$pickItem->game->title.'</h3>'."\n";
            $gameHtml .= '[gameheader ids="'.$gameId.'"]'."\n";
            $autoDescription = $this->autoDescription->generate($pickItem->game);
            $gameHtml .= '<p>'.$autoDescription.'</p>';
            /*
            $gameHtml .= '<p>'.$pickItem->game->title.' has '.$pickItem->game->review_count.' review(s), '.
                'with an average rating of '.round($pickItem->game->rating_avg, 2).'.'.
                '</p>';
            */
        }

        // 3. Compose body text
        $body = <<<MD
<p>
These picks have strong averages but only 2 reviews. If you’ve played them, add your review and help them enter the rankings.
</p>
<p>
<strong>We skip low quality and de-listed titles.</strong>
</p>
{$gameHtml}
MD;

        // 4. Create the draft post
        $title = 'Games that need one more review: ' . now()->format('F j, Y');
        $url = '/news/'.now()->format('Y-m-d').'/games-that-need-one-more-review';
        $post = News::create([
            'title'  => $title,
            'url' => $url,
            'content_html'  => $body,
            'category_id' => 8, // featured games
            'game_id' => $firstGameId == null ? null : $firstGameId,
            //'status' => 'draft',     // adjust to your field names
        ]);

        // 5. Mark picks as used
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
