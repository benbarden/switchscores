<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\BrowserKit\HttpBrowser;

use App\Domain\Game\Repository as GameRepository;
use App\Models\GameCrawlLifecycle;

class GameCrawlBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:crawl-batch
                            {--limit=50 : Number of games to process}
                            {--delay=2 : Seconds to wait between requests}
                            {--source=override : Source to crawl: override, datasource, or all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batch crawl Nintendo URLs for multiple games, checking status codes.';

    private array $results = [
        'success' => 0,
        'not_found' => 0,
        'gone' => 0,
        'redirect' => 0,
        'error' => 0,
        'no_url' => 0,
    ];

    private array $problemGames = [];

    public function __construct(
        private GameRepository $repoGame
    )
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $delay = (int) $this->option('delay');
        $source = $this->option('source');

        $logger = Log::channel('cron');
        $logger->info(' *************** '.$this->signature.' *************** ');
        $logger->info("Options: limit={$limit}, delay={$delay}s, source={$source}");

        $this->info("Starting batch crawl: {$limit} games, {$delay}s delay, source={$source}");
        $this->newLine();

        // Get games to crawl
        $games = $this->getGamesToCrawl($source, $limit);
        $total = count($games);

        if ($total === 0) {
            $this->warn("No games found to crawl");
            return 0;
        }

        $this->info("Found {$total} games to crawl");
        $this->newLine();

        $httpBrowser = new HttpBrowser();
        $processed = 0;

        foreach ($games as $game) {
            $processed++;
            $this->crawlGame($game, $httpBrowser, $logger, $processed, $total);

            // Rate limiting - don't delay after the last one
            if ($processed < $total) {
                sleep($delay);
            }
        }

        // Summary
        $this->newLine();
        $this->displaySummary($logger);

        $logger->info('Complete');
        return 0;
    }

    /**
     * Get games to crawl, prioritising those never crawled or crawled longest ago.
     */
    private function getGamesToCrawl(string $source, int $limit): array
    {
        $query = DB::table('games')
            ->select('id', 'title', 'nintendo_store_url_override')
            ->where('game_status', 'active');

        // Filter by source
        if ($source === 'override') {
            $query->whereNotNull('nintendo_store_url_override');
        } elseif ($source === 'datasource') {
            $query->whereNull('nintendo_store_url_override')
                  ->whereExists(function ($q) {
                      $q->select(DB::raw(1))
                        ->from('data_source_parsed')
                        ->whereColumn('data_source_parsed.game_id', 'games.id')
                        ->whereNotNull('data_source_parsed.url');
                  });
        }
        // 'all' doesn't add extra filters

        // Prioritise: override URLs first (most likely broken), then never crawled, then oldest crawled, then oldest games
        $query->orderByRaw('nintendo_store_url_override IS NOT NULL DESC')
              ->orderByRaw('last_crawled_at IS NULL DESC')
              ->orderBy('last_crawled_at', 'asc')
              ->orderBy('id', 'asc')
              ->limit($limit);

        return $query->get()->all();
    }

    /**
     * Crawl a single game.
     */
    private function crawlGame(object $gameRow, HttpBrowser $httpBrowser, $logger, int $current, int $total): void
    {
        $game = $this->repoGame->find($gameRow->id);
        if (!$game) {
            $this->results['error']++;
            return;
        }

        $url = $this->getNintendoUrl($game);
        if (!$url) {
            $this->line("[{$current}/{$total}] {$game->title} - No URL");
            $this->results['no_url']++;
            return;
        }

        try {
            $crawler = $httpBrowser->request('GET', $url);
            $response = $httpBrowser->getResponse();
            $statusCode = $response->getStatusCode();

            // Capture previous status before updating
            $previousStatus = $game->last_crawl_status;

            // Update game record
            $game->last_crawled_at = now();
            $game->last_crawl_status = $statusCode;
            $game->save();

            // Clear cache so updated data is visible immediately
            $this->repoGame->clearCacheCoreData($game->id);

            // Log to lifecycle table if it's a problem or recovery
            $this->logLifecycleEvent($game, $statusCode, $previousStatus, $url);

            // Categorise result for summary
            $this->recordResult($game, $statusCode, $url);

            // Display
            $statusEmoji = $this->getStatusEmoji($statusCode);
            $this->line("[{$current}/{$total}] {$statusEmoji} {$statusCode} - {$game->title}");

            $logger->info("Game {$game->id}: {$statusCode} - {$game->title}");

        } catch (\Exception $e) {
            $this->results['error']++;
            $this->problemGames[] = [
                'id' => $game->id,
                'title' => $game->title,
                'status' => 'ERROR',
                'url' => $url,
                'message' => $e->getMessage(),
            ];
            $this->line("[{$current}/{$total}] ERROR - {$game->title}: {$e->getMessage()}");
            $logger->error("Game {$game->id} error: {$e->getMessage()}");
        }
    }

    /**
     * Get the Nintendo UK URL for a game.
     */
    private function getNintendoUrl($game): ?string
    {
        if ($game->nintendo_store_url_override) {
            return $game->nintendo_store_url_override;
        }

        $dsItem = $game->dspNintendoCoUk()->first();
        if ($dsItem && $dsItem->url) {
            return 'https://www.nintendo.com/' . $dsItem->url;
        }

        return null;
    }

    /**
     * Log to lifecycle table if it's a problem or recovery.
     * - Non-200 status: always log (problem detected)
     * - 200 status after non-200: log (recovery)
     * - 200 status after 200 or null: skip (no news)
     */
    private function logLifecycleEvent($game, int $statusCode, ?int $previousStatus, string $url): void
    {
        $shouldLog = false;

        if ($statusCode !== 200) {
            // It's a problem - always log
            $shouldLog = true;
        } elseif ($previousStatus !== null && $previousStatus !== 200) {
            // It's a recovery (was broken, now fixed) - log it
            $shouldLog = true;
        }

        if ($shouldLog) {
            GameCrawlLifecycle::create([
                'game_id' => $game->id,
                'status_code' => $statusCode,
                'url_crawled' => $url,
                'crawled_at' => now(),
            ]);
        }
    }

    /**
     * Record the result of a crawl.
     */
    private function recordResult($game, int $statusCode, string $url): void
    {
        if ($statusCode === 200) {
            $this->results['success']++;
        } elseif ($statusCode === 404) {
            $this->results['not_found']++;
            $this->problemGames[] = [
                'id' => $game->id,
                'title' => $game->title,
                'status' => '404',
                'url' => $url,
            ];
        } elseif ($statusCode === 410) {
            $this->results['gone']++;
            $this->problemGames[] = [
                'id' => $game->id,
                'title' => $game->title,
                'status' => '410',
                'url' => $url,
            ];
        } elseif ($statusCode >= 300 && $statusCode < 400) {
            $this->results['redirect']++;
            $this->problemGames[] = [
                'id' => $game->id,
                'title' => $game->title,
                'status' => (string) $statusCode,
                'url' => $url,
            ];
        } else {
            $this->results['error']++;
            $this->problemGames[] = [
                'id' => $game->id,
                'title' => $game->title,
                'status' => (string) $statusCode,
                'url' => $url,
            ];
        }
    }

    /**
     * Get emoji for status code.
     */
    private function getStatusEmoji(int $statusCode): string
    {
        return match(true) {
            $statusCode === 200 => '✓',
            $statusCode === 404 => '✗',
            $statusCode === 410 => '☠',
            $statusCode >= 300 && $statusCode < 400 => '→',
            default => '?',
        };
    }

    /**
     * Display summary of results.
     */
    private function displaySummary($logger): void
    {
        $this->info('=== SUMMARY ===');
        $this->line("Success (200):    {$this->results['success']}");
        $this->line("Not Found (404):  {$this->results['not_found']}");
        $this->line("Gone (410):       {$this->results['gone']}");
        $this->line("Redirects:        {$this->results['redirect']}");
        $this->line("Errors:           {$this->results['error']}");
        $this->line("No URL:           {$this->results['no_url']}");

        $logger->info('Summary: ' . json_encode($this->results));

        if (count($this->problemGames) > 0) {
            $this->newLine();
            $this->warn('=== PROBLEM GAMES ===');
            foreach ($this->problemGames as $problem) {
                $this->line("[{$problem['status']}] ID:{$problem['id']} - {$problem['title']}");
                $this->line("    URL: {$problem['url']}");
                if (isset($problem['message'])) {
                    $this->line("    Error: {$problem['message']}");
                }
            }

            $logger->warning('Problem games: ' . json_encode($this->problemGames));
        }
    }
}
