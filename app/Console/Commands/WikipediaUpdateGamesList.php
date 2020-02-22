<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\GameImportRuleWikipedia;

use App\Events\GameCreated;

use App\Services\UrlService;

use App\Construction\Game\GameDirector;
use App\Construction\Game\GameBuilder;

use App\Traits\SwitchServices;

class WikipediaUpdateGamesList extends Command
{
    use SwitchServices;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WikipediaUpdateGamesList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates games from the feed items data.';

    /**
     * WikipediaUpdateGamesList constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $logger = Log::channel('cron');

        $logger->info(' *************** '.$this->signature.' *************** ');

        $logger->info('Loading source data...');

        $gameDirector = new GameDirector();
        $gameBuilder = new GameBuilder();

        $serviceGame = $this->getServiceGame();
        $serviceFeedItemGame = $this->getServiceFeedItemGame();
        $serviceGameTitleHash = $this->getServiceGameTitleHash();

        $feedItemsList = $serviceFeedItemGame->getForProcessing();

        if (count($feedItemsList) == 0) {
            $logger->info('No items found - aborting');
            return;
        }

        $logger->info('Found '.count($feedItemsList).' item(s)');

        foreach ($feedItemsList as $feedItem) {

            $gameId = $feedItem->game_id;

            if ($gameId) {

                // Existing game
                $game = $serviceGame->find($gameId);
                if (!$game) {
                    $logger->info('Game not found: '.$gameId.' ; skipping');
                    continue;
                }

                // Get game import rule
                $gameImportRule = $this->getServiceGameImportRuleWikipedia()->getByGameId($gameId);
                if (!$gameImportRule) {
                    $gameImportRule = new GameImportRuleWikipedia;
                }

                $gameChanged = false;

                // Developers
                if (!$gameImportRule->shouldIgnoreDevelopers()) {
                    if ($game->gameDevelopers()->count() == 0) {
                        // Only proceed if new developer db entries do not exist
                        if ($game->developer != $feedItem->item_developers) {
                            $newDeveloper = $feedItem->item_developers;
                            $newDeveloper = str_replace("\r", ' ', $newDeveloper);
                            $newDeveloper = str_replace("\n", ' ', $newDeveloper);
                            $game->developer = $newDeveloper;
                            $gameChanged = true;
                        }
                    }
                }

                // Publishers
                if (!$gameImportRule->shouldIgnorePublishers()) {
                    if ($game->gamePublishers()->count() == 0) {
                        // Only proceed if new publisher db entries do not exist
                        if ($game->publisher != $feedItem->item_publishers) {
                            $newPublisher = $feedItem->item_publishers;
                            $newPublisher = str_replace("\r", ' ', $newPublisher);
                            $newPublisher = str_replace("\n", ' ', $newPublisher);
                            $game->publisher = $newPublisher;
                            $gameChanged = true;
                        }
                    }
                }

                // Europe release date
                if (!$gameImportRule->shouldIgnoreEuropeDates()) {
                    if ($game->eu_release_date != $feedItem->release_date_eu) {
                        $game->eu_release_date = $feedItem->release_date_eu;
                        $gameChanged = true;
                    }
                }

                // US release date
                if (!$gameImportRule->shouldIgnoreUSDates()) {
                    if ($game->us_release_date != $feedItem->release_date_us) {
                        $game->us_release_date = $feedItem->release_date_us;
                        $gameChanged = true;
                    }
                }

                // Japan release date
                if (!$gameImportRule->shouldIgnoreJPDates()) {
                    if ($game->jp_release_date != $feedItem->release_date_jp) {
                        $game->jp_release_date = $feedItem->release_date_jp;
                        $gameChanged = true;
                    }
                }

                if ($gameChanged) {

                    $logger->info('Saving updates to game');
                    $game->save();

                }

                $logger->info('Marking feed item as complete');
                $feedItem->setStatusComplete();
                $feedItem->save();

            } else {

                $logger->info('Creating game...');

                $serviceUrl = new UrlService();

                $title = $feedItem->item_title;

                $titleLowercase = strtolower($title);
                $hashedTitle = $serviceGameTitleHash->generateHash($title);
                $gameTitleHash = $serviceGameTitleHash->getByHash($hashedTitle);
                if ($gameTitleHash) {
                    $logger->warn('Title hash already exists - cannot create game: '.$title.'; skipping');
                    continue;
                }

                // New game
                $linkTitle = $serviceUrl->generateLinkText($title);

                $newDeveloper = $feedItem->item_developers;
                $newDeveloper = str_replace("\r", ' ', $newDeveloper);
                $newDeveloper = str_replace("\n", ' ', $newDeveloper);
                $developers = $newDeveloper;

                $newPublisher = $feedItem->item_publishers;
                $newPublisher = str_replace("\r", ' ', $newPublisher);
                $newPublisher = str_replace("\n", ' ', $newPublisher);
                $publishers = $newPublisher;

                // Add game
                $gameBuilder->reset();
                $gameDirector->setBuilder($gameBuilder);
                $newGameParams = [
                    'title' => $title,
                    'link_title' => $linkTitle,
                    'developer' => $developers,
                    'publisher' => $publishers,
                    'eu_release_date' => $feedItem->release_date_eu,
                    'us_release_date' => $feedItem->release_date_us,
                    'jp_release_date' => $feedItem->release_date_jp,
                ];
                // Check price_eshop and players are both set to null
                $gameDirector->buildNewGame($newGameParams);
                $game = $gameBuilder->getGame();
                $game->save();
                $gameId = $game->id;

                // Create title hash
                $serviceGameTitleHash->create($titleLowercase, $hashedTitle, $gameId);

                // Wrapping up
                $logger->info('Marking feed item as complete');
                $feedItem->game_id = $gameId;
                $feedItem->setStatusComplete();
                $feedItem->save();

                event(new GameCreated($game));

            }

        }
    }
}
