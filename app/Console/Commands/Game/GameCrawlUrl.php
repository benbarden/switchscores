<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\BrowserKit\HttpBrowser;

use App\Domain\Game\Repository as GameRepository;
use App\Models\Game;
use App\Models\GameCrawlLifecycle;

class GameCrawlUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:crawl {gameId} {--save-html}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crawls a game\'s Nintendo UK URL and checks its status.';

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
        $gameId = $this->argument('gameId');
        $saveHtml = $this->option('save-html');

        $logger = Log::channel('cron');
        $logger->info(' *************** '.$this->signature.' *************** ');

        // Find the game
        $game = $this->repoGame->find($gameId);
        if (!$game) {
            $this->error("Game not found: {$gameId}");
            $logger->error("Game not found: {$gameId}");
            return 1;
        }

        $this->info("Game: {$game->title} [{$game->id}]");
        $logger->info("Game: {$game->title} [{$game->id}]");

        // Get the Nintendo URL
        $url = $this->getNintendoUrl($game);
        if (!$url) {
            $this->error("No Nintendo URL available for this game");
            $logger->error("No Nintendo URL available for game: {$game->id}");
            return 1;
        }

        $this->info("URL: {$url}");
        $logger->info("URL: {$url}");

        // Crawl the URL
        try {
            $httpBrowser = new HttpBrowser();
            $crawler = $httpBrowser->request('GET', $url);

            $response = $httpBrowser->getResponse();
            $statusCode = $response->getStatusCode();

            $this->info("Status: {$statusCode}");
            $logger->info("Status: {$statusCode}");

            // Save HTML if requested
            if ($saveHtml) {
                $this->saveHtml($game, $crawler->html(), $statusCode);
            }

            // Capture previous status before updating
            $previousStatus = $game->last_crawl_status;

            // Update game record
            $game->last_crawled_at = now();
            $game->last_crawl_status = $statusCode;
            $game->save();

            $this->info("Updated last_crawled_at and last_crawl_status");
            $logger->info("Updated crawl fields for game: {$game->id}");

            // Log to lifecycle table if it's a problem or recovery
            $this->logLifecycleEvent($game, $statusCode, $previousStatus, $url, $logger);

            // Report on status
            $this->reportStatus($statusCode);

        } catch (\Exception $e) {
            $this->error("Crawl failed: " . $e->getMessage());
            $logger->error("Crawl failed for game {$game->id}: " . $e->getMessage());

            if ($saveHtml) {
                $this->saveErrorLog($game, $e->getMessage());
            }

            return 1;
        }

        $logger->info('Complete');
        return 0;
    }

    /**
     * Get the Nintendo UK URL for a game.
     */
    private function getNintendoUrl(Game $game): ?string
    {
        // First check for override URL
        if ($game->nintendo_store_url_override) {
            return $game->nintendo_store_url_override;
        }

        // Then check for DataSourceParsed item
        $dsItem = $game->dspNintendoCoUk()->first();
        if ($dsItem && $dsItem->url) {
            return 'https://www.nintendo.com/' . $dsItem->url;
        }

        return null;
    }

    /**
     * Save HTML to storage for debugging.
     */
    private function saveHtml(Game $game, string $html, int $statusCode): void
    {
        $filename = "crawl-debug/game-{$game->id}-{$statusCode}-" . date('Y-m-d-His') . ".html";
        Storage::disk('local')->put($filename, $html);
        $this->info("Saved HTML to: storage/app/{$filename}");
    }

    /**
     * Save error log to storage.
     */
    private function saveErrorLog(Game $game, string $error): void
    {
        $filename = "crawl-debug/game-{$game->id}-error-" . date('Y-m-d-His') . ".txt";
        Storage::disk('local')->put($filename, $error);
        $this->info("Saved error log to: storage/app/{$filename}");
    }

    /**
     * Report on the status code meaning.
     */
    private function reportStatus(int $statusCode): void
    {
        $message = match(true) {
            $statusCode === 200 => "Page is live and accessible",
            $statusCode === 301 => "Permanent redirect - URL may have changed",
            $statusCode === 302 => "Temporary redirect",
            $statusCode === 404 => "Page not found - game may be de-listed",
            $statusCode === 410 => "Gone - game has been removed",
            $statusCode >= 500 => "Server error - temporary issue",
            default => "Unexpected status code"
        };

        $this->info("Result: {$message}");
    }

    /**
     * Log to lifecycle table if it's a problem or recovery.
     */
    private function logLifecycleEvent(Game $game, int $statusCode, ?int $previousStatus, string $url, $logger): void
    {
        $shouldLog = false;
        $eventType = '';

        if ($statusCode !== 200) {
            $shouldLog = true;
            $eventType = 'problem';
        } elseif ($previousStatus !== null && $previousStatus !== 200) {
            $shouldLog = true;
            $eventType = 'recovery';
        }

        if ($shouldLog) {
            GameCrawlLifecycle::create([
                'game_id' => $game->id,
                'status_code' => $statusCode,
                'url_crawled' => $url,
                'crawled_at' => now(),
            ]);
            $this->info("Logged lifecycle event: {$eventType}");
            $logger->info("Logged lifecycle event for game {$game->id}: {$eventType}");
        }
    }
}
