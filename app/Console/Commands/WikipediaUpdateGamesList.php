<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Game;
use App\GameTitleHash;
use App\FeedItemGame;

use App\Events\GameCreated;

use App\Services\UrlService;
use App\Services\GameService;
use App\Services\GameReleaseDateService;
use App\Services\GameTitleHashService;
use App\Services\FeedItemGameService;
use App\Services\GameChangeHistoryService;

use App\Construction\Game\GameDirector;
use App\Construction\Game\GameBuilder;
use App\Construction\GameChangeHistory\Director as GameChangeHistoryDirector;
use App\Construction\GameChangeHistory\Builder as GameChangeHistoryBuilder;

class WikipediaUpdateGamesList extends Command
{
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
     * @return mixed
     */
    public function handle()
    {
        $this->info(' *** '.$this->signature.' ['.date('Y-m-d H:i:s').']'.' *** ');

        $this->info('Loading source data...');

        $gameDirector = new GameDirector();
        $gameBuilder = new GameBuilder();

        $feedItemGameService = resolve('Services\FeedItemGameService');
        /* @var FeedItemGameService $feedItemGameService */
        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $gameTitleHashService = resolve('Services\GameTitleHashService');
        /* @var GameTitleHashService $gameTitleHashService */
        $gameReleaseDateService = resolve('Services\GameReleaseDateService');
        /* @var GameReleaseDateService $gameReleaseDateService */

        $serviceGameChangeHistory = resolve('Services\GameChangeHistoryService');
        /* @var GameChangeHistoryService $serviceGameChangeHistory */

        $feedItemsList = $feedItemGameService->getForProcessing();

        if (count($feedItemsList) == 0) {
            $this->info('No items found - aborting');
            return;
        }

        $this->info('Found '.count($feedItemsList).' item(s)');

        foreach ($feedItemsList as $feedItem) {

            $gameId = $feedItem->game_id;

            if ($gameId) {

                // Existing game
                $game = $gameService->find($gameId);
                if (!$game) {
                    $this->info('Game not found: '.$gameId.' ; skipping');
                    continue;
                }

                // Check standard fields
                $gameChanged = false;
                if ($game->developer != $feedItem->item_developers) {
                    $newDeveloper = $feedItem->item_developers;
                    $newDeveloper = str_replace("\r", ' ', $newDeveloper);
                    $newDeveloper = str_replace("\n", ' ', $newDeveloper);
                    $game->developer = $newDeveloper;
                    $gameChanged = true;
                }
                if ($game->publisher != $feedItem->item_publishers) {
                    $newPublisher = $feedItem->item_publishers;
                    $newPublisher = str_replace("\r", ' ', $newPublisher);
                    $newPublisher = str_replace("\n", ' ', $newPublisher);
                    $game->publisher = $newPublisher;
                    $gameChanged = true;
                }

                // Release dates
                $gameReleaseDates = $gameReleaseDateService->getByGame($gameId);
                if (!$gameReleaseDates) {
                    $this->info('No release dates for game: '.$gameId.' ; skipping');
                    continue;
                }

                foreach ($gameReleaseDates as $gameReleaseDate) {

                    $region = $gameReleaseDate->region;

                    $fieldReleaseDate = 'release_date_'.$region;
                    $fieldUpcomingDate = 'upcoming_date_'.$region;
                    $fieldIsReleased = 'is_released_'.$region;

                    $gameReleaseDateChanged = false;
                    if ($gameReleaseDate->release_date != $feedItem->{$fieldReleaseDate}) {
                        $gameReleaseDate->release_date = $feedItem->{$fieldReleaseDate};
                        $gameReleaseDate->release_year = $gameReleaseDateService->getReleaseYear($gameReleaseDate->release_date);
                        $gameReleaseDateChanged = true;
                    }
                    if ($gameReleaseDate->upcoming_date != $feedItem->{$fieldUpcomingDate}) {
                        $gameReleaseDate->upcoming_date = $feedItem->{$fieldUpcomingDate};
                        $gameReleaseDateChanged = true;
                    }

                    if ($gameReleaseDateChanged) {
                        $this->info('Saving updates to region: '.$region);
                        $gameReleaseDate->save();
                    }

                }

                if ($gameChanged) {

                    // Recreate objects each time to avoid issues
                    $gameChangeHistoryDirector = new GameChangeHistoryDirector();
                    $gameChangeHistoryBuilder = new GameChangeHistoryBuilder();

                    // Get original version before saving
                    $gameOrig = $game->fresh();

                    $this->info('Saving updates to game');
                    $game->save();

                    // Game change history
                    //$gameChangeHistoryBuilder->reset(); // not needed when reinstantiating
                    $gameChangeHistoryBuilder->setGame($game);
                    $gameChangeHistoryBuilder->setGameOriginal($gameOrig);
                    $gameChangeHistoryDirector->setBuilder($gameChangeHistoryBuilder);
                    $gameChangeHistoryDirector->setTableNameGames();
                    $gameChangeHistoryDirector->buildWikipediaUpdate();
                    $gameChangeHistory = $gameChangeHistoryBuilder->getGameChangeHistory();
                    $gameChangeHistory->save();

                }

                $this->info('Marking feed item as complete');
                $feedItem->setStatusComplete();
                $feedItem->save();

            } else {

                $this->info('Creating game...');

                $serviceUrl = new UrlService();

                $title = $feedItem->item_title;

                $titleHash = $gameTitleHashService->generateHash($title);
                $gameTitleHash = $gameTitleHashService->getByHash($titleHash);
                if ($gameTitleHash) {
                    $this->warn('Title hash already exists - cannot create game: '.$title.'; skipping');
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
                ];
                // Check price_eshop and players are both set to null
                $gameDirector->buildNewGame($newGameParams);
                $game = $gameBuilder->getGame();
                $game->save();
                $gameId = $game->id;

                // Create title hash
                $gameTitleHashService->create($title, $titleHash, $gameId);

                // Release dates
                $regions = ['eu', 'us', 'jp'];
                foreach ($regions as $region) {

                    $fieldReleaseDate = 'release_date_'.$region;
                    $fieldUpcomingDate = 'upcoming_date_'.$region;
                    $fieldIsReleased = 'is_released_'.$region;

                    $releaseDateValue = $feedItem->{$fieldReleaseDate};
                    $upcomingDateValue = $feedItem->{$fieldUpcomingDate};
                    $isReleasedValue = $feedItem->{$fieldIsReleased} == 1 ? 'on' : 'off';

                    $gameReleaseDateService->createGameReleaseDate($gameId, $region, $releaseDateValue, $isReleasedValue, $upcomingDateValue);

                }

                // Recreate objects each time to avoid issues
                $gameChangeHistoryDirector = new GameChangeHistoryDirector();
                $gameChangeHistoryBuilder = new GameChangeHistoryBuilder();
                
                // Game change history
                $gameChangeHistoryBuilder->reset();
                $gameChangeHistoryBuilder->setGame($game);
                $gameChangeHistoryDirector->setBuilder($gameChangeHistoryBuilder);
                $gameChangeHistoryDirector->setTableNameGames();
                $gameChangeHistoryDirector->buildWikipediaInsert();
                $gameChangeHistory = $gameChangeHistoryBuilder->getGameChangeHistory();
                $gameChangeHistory->save();

                // Wrapping up
                $this->info('Marking feed item as complete');
                $feedItem->game_id = $gameId;
                $feedItem->setStatusComplete();
                $feedItem->save();

                event(new GameCreated($game));

            }

        }
    }
}
