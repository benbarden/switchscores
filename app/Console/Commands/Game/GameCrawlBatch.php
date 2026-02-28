<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\BrowserKit\HttpBrowser;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Scraper\NintendoCoUkGameData;
use App\Models\GameCrawlLifecycle;
use App\Models\GameScrapedData;

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

        // Prioritise:
        // 1. Priority flag (newly imported games)
        // 2. Games with override URLs (most likely to have issues)
        // 3. Never crawled (newest first)
        // 4. Games with null players (need data populated)
        // 5. Oldest crawled
        $query->orderByRaw('crawl_priority DESC')
              ->orderByRaw('nintendo_store_url_override IS NOT NULL DESC')
              ->orderByRaw('last_crawled_at IS NULL DESC')
              ->orderBy('created_at', 'desc')
              ->orderByRaw('players IS NULL DESC')
              ->orderBy('last_crawled_at', 'asc')
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

        // Show which game we're crawling
        $priorityTag = $game->crawl_priority ? ' [PRIORITY]' : '';
        $this->line("[{$current}/{$total}] Crawling: {$game->id} - {$game->title}{$priorityTag}");

        $url = $this->getNintendoUrl($game);
        if (!$url) {
            $this->line("  No URL available");
            $this->results['no_url']++;
            return;
        }

        try {
            $crawler = $httpBrowser->request('GET', $url);
            $response = $httpBrowser->getResponse();
            $statusCode = $response->getStatusCode();
            $isSoft404 = false;

            // Check for soft 404 (redirected to 404 page but got 200 status)
            $finalUrl = $httpBrowser->getHistory()->current()->getUri();
            if ($statusCode === 200 && $this->isSoft404($finalUrl)) {
                $statusCode = 404;
                $isSoft404 = true;
                $this->warn("  Soft 404 detected (redirected to: {$finalUrl})");
                $logger->warning("Soft 404 detected for game {$game->id}: {$finalUrl}");
            }

            // Capture previous status before updating
            $previousStatus = $game->last_crawl_status;

            // Update game record
            $game->last_crawled_at = now();
            $game->last_crawl_status = $statusCode;
            $game->crawl_priority = false;
            $game->save();

            // Clear cache so updated data is visible immediately
            $this->repoGame->clearCacheCoreData($game->id);

            // Log to lifecycle table if it's a problem or recovery
            $this->logLifecycleEvent($game, $statusCode, $previousStatus, $url);

            // Scrape game data if page is live
            $scrapeWarnings = [];
            if ($statusCode === 200) {
                $scrapeWarnings = $this->scrapeGameData($game, $crawler->html(), $logger);
                foreach ($scrapeWarnings as $warning) {
                    $this->warn("  {$warning}");
                }
            }

            // Categorise result for summary
            $this->recordResult($game, $statusCode, $url);

            // Display final status
            $statusEmoji = $this->getStatusEmoji($statusCode);
            $this->line("  {$statusEmoji} Status: {$statusCode}");

        } catch (\Exception $e) {
            $this->results['error']++;
            $this->problemGames[] = [
                'id' => $game->id,
                'title' => $game->title,
                'status' => 'ERROR',
                'url' => $url,
                'message' => $e->getMessage(),
            ];
            $this->error("  ERROR: {$e->getMessage()}");
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

    /**
     * Scrape game data from the HTML and save to database.
     * Returns array of warnings (e.g., fields still missing).
     */
    private function scrapeGameData($game, string $html, $logger): array
    {
        $warnings = [];
        $playersWasNull = $game->players === null;

        try {
            $scraper = new NintendoCoUkGameData($html);

            if (!$scraper->hasData()) {
                if ($playersWasNull) {
                    $warnings[] = 'players still NULL - no data found';
                }
                return $warnings;
            }

            // Update or create the scraped data record
            GameScrapedData::updateOrCreate(
                ['game_id' => $game->id],
                [
                    'players_local' => $scraper->getPlayersLocal(),
                    'players_wireless' => $scraper->getPlayersWireless(),
                    'players_online' => $scraper->getPlayersOnline(),
                    'multiplayer_mode' => $scraper->getMultiplayerMode(),
                    'features_json' => $scraper->getFeatures(),
                    'scraped_at' => now(),
                ]
            );

            // Update game fields from scraped data
            $this->updateGameFromScrapedData($game, $scraper);

            // Check if players is still null after scraping
            $game->refresh();
            if ($playersWasNull && $game->players === null) {
                $warnings[] = 'players still NULL';
            }

        } catch (\Exception $e) {
            $logger->warning("Failed to scrape game data for game {$game->id}: " . $e->getMessage());
            $warnings[] = 'scrape error';
        }

        return $warnings;
    }

    /**
     * Update game fields from scraped data.
     */
    private function updateGameFromScrapedData($game, NintendoCoUkGameData $scraper): void
    {
        $hasChanges = false;

        // Players field
        $combinedPlayers = $scraper->getCombinedPlayers();
        if ($combinedPlayers !== null && $game->players !== $combinedPlayers) {
            $game->players = $combinedPlayers;
            $hasChanges = true;
        }

        // Multiplayer mode
        $multiplayerMode = $scraper->getMultiplayerMode();
        if ($multiplayerMode !== null && $game->multiplayer_mode !== $multiplayerMode) {
            $game->multiplayer_mode = $multiplayerMode;
            $hasChanges = true;
        }

        // Boolean flags - only update if scraper found features data
        if (!empty($scraper->getFeatures()) || $scraper->hasPlayerData()) {
            $booleanFields = [
                'has_online_play' => $scraper->hasOnlinePlay(),
                'has_local_multiplayer' => $scraper->hasLocalMultiplayer(),
                'play_mode_tv' => $scraper->hasPlayModeTv(),
                'play_mode_tabletop' => $scraper->hasPlayModeTabletop(),
                'play_mode_handheld' => $scraper->hasPlayModeHandheld(),
            ];

            foreach ($booleanFields as $field => $newValue) {
                $currentValue = (bool) $game->{$field};
                if ($currentValue !== $newValue) {
                    $game->{$field} = $newValue;
                    $hasChanges = true;
                }
            }
        }

        // Save if changed
        if ($hasChanges) {
            $game->save();
            $this->repoGame->clearCacheCoreData($game->id);
        }
    }

    /**
     * Check if the final URL indicates a soft 404 (redirected to error page).
     */
    private function isSoft404(string $finalUrl): bool
    {
        // Nintendo's 404 page URL patterns
        $soft404Patterns = [
            '/404.html',
            '/404',
            '/en-gb/404',
            '/en-gb/404.html',
        ];

        foreach ($soft404Patterns as $pattern) {
            if (str_contains($finalUrl, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
