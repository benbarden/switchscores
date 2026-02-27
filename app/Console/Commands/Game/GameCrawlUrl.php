<?php

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\BrowserKit\HttpBrowser;

use App\Domain\Game\Repository as GameRepository;
use App\Domain\Scraper\NintendoCoUkGameData;
use App\Models\Game;
use App\Models\GameCrawlLifecycle;
use App\Models\GameScrapedData;

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

            // Check for soft 404 (redirected to 404 page but got 200 status)
            $finalUrl = $httpBrowser->getHistory()->current()->getUri();
            if ($statusCode === 200 && $this->isSoft404($finalUrl)) {
                $statusCode = 404;
                $this->warn("Detected soft 404 (redirected to: {$finalUrl})");
                $logger->warning("Soft 404 detected for game {$game->id}: {$finalUrl}");
            }

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

            // Clear cache so updated data is visible immediately
            $this->repoGame->clearCacheCoreData($game->id);

            $this->info("Updated last_crawled_at and last_crawl_status");
            $logger->info("Updated crawl fields for game: {$game->id}");

            // Log to lifecycle table if it's a problem or recovery
            $this->logLifecycleEvent($game, $statusCode, $previousStatus, $url, $logger);

            // Scrape game data if page is live
            if ($statusCode === 200) {
                $this->scrapeGameData($game, $crawler->html(), $logger);
            }

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

    /**
     * Scrape game data from the HTML and save to database.
     */
    private function scrapeGameData(Game $game, string $html, $logger): void
    {
        try {
            $scraper = new NintendoCoUkGameData($html);

            if (!$scraper->hasData()) {
                $this->info("No player/multiplayer data found on page");
                $logger->info("No player/multiplayer data found for game: {$game->id}");
                return;
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

            $this->info("Scraped game data saved");
            $logger->info("Scraped game data saved for game: {$game->id}");

            // Log what was found
            $data = $scraper->getData();
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $this->info("  {$key}: {$value}");
            }

            // Update game fields from scraped data
            $this->updateGameFromScrapedData($game, $scraper, $logger);

        } catch (\Exception $e) {
            $this->warn("Failed to scrape game data: " . $e->getMessage());
            $logger->warning("Failed to scrape game data for game {$game->id}: " . $e->getMessage());
        }
    }

    /**
     * Update game fields from scraped data.
     */
    private function updateGameFromScrapedData(Game $game, NintendoCoUkGameData $scraper, $logger): void
    {
        $updates = [];
        $changes = [];

        // Players field
        $combinedPlayers = $scraper->getCombinedPlayers();
        if ($combinedPlayers !== null && $game->players !== $combinedPlayers) {
            $oldValue = $game->players ?? '(empty)';
            $updates['players'] = $combinedPlayers;
            $changes[] = "players: {$oldValue} -> {$combinedPlayers}";
        }

        // Multiplayer mode
        $multiplayerMode = $scraper->getMultiplayerMode();
        if ($multiplayerMode !== null && $game->multiplayer_mode !== $multiplayerMode) {
            $oldValue = $game->multiplayer_mode ?? '(empty)';
            $updates['multiplayer_mode'] = $multiplayerMode;
            $changes[] = "multiplayer_mode: {$oldValue} -> {$multiplayerMode}";
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
                    $updates[$field] = $newValue;
                    $oldStr = $currentValue ? 'true' : 'false';
                    $newStr = $newValue ? 'true' : 'false';
                    $changes[] = "{$field}: {$oldStr} -> {$newStr}";
                }
            }
        }

        // Apply updates if any
        if (empty($updates)) {
            $this->info("Game fields unchanged");
            return;
        }

        foreach ($updates as $field => $value) {
            $game->{$field} = $value;
        }
        $game->save();

        // Clear cache since we updated game data
        $this->repoGame->clearCacheCoreData($game->id);

        foreach ($changes as $change) {
            $this->info("Updated: {$change}");
        }
        $logger->info("Updated game {$game->id}: " . implode(', ', $changes));
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
