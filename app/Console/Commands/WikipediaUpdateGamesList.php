<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\FeedItemGame;
use App\Game;
use App\GameTitleHash;
use App\Services\UrlService;
use App\Services\GameService;
use App\Services\GameReleaseDateService;
use App\Services\GameTitleHashService;
use App\Services\FeedItemGameService;
use App\Events\GameCreated;

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
        $this->info('Loading source data...');

        $feedItemGameService = resolve('Services\FeedItemGameService');
        /* @var FeedItemGameService $feedItemGameService */
        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $gameTitleHashService = resolve('Services\GameTitleHashService');
        /* @var GameTitleHashService $gameTitleHashService */
        $gameReleaseDateService = resolve('Services\GameReleaseDateService');
        /* @var GameReleaseDateService $gameReleaseDateService */

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
                $game = $feedItemGameService->find($gameId);
                if (!$game) {
                    $this->info('Game not found: '.$gameId.' ; skipping');
                    continue;
                }

                // Check standard fields
                $gameChanged = false;
                if ($game->developer != $feedItem->developers) {
                    $game->developer = $feedItem->developers;
                    $gameChanged = true;
                }
                if ($game->publisher != $feedItem->publishers) {
                    $game->publisher = $feedItem->publishers;
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
                    if ($gameReleaseDate->is_released != $feedItem->{$fieldIsReleased}) {
                        $gameReleaseDate->is_released = $feedItem->{$fieldIsReleased};
                        $gameReleaseDateChanged = true;
                    }

                    if ($gameReleaseDateChanged) {
                        $this->info('Saving updates to region: '.$region);
                        $gameReleaseDate->save();
                    }

                }

                if ($gameChanged) {
                    $this->info('Saving updates to game');
                    $game->save();
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
                $developers = $feedItem->developers;
                $publishers = $feedItem->publishers;

                $game = $gameService->create($title, $linkTitle, null, null, $developers, $publishers, null, null, null, null);

                $gameId = $game->id;

                // Create title hash
                $gameTitleHashService->create($title, $titleHash, $gameId);

                // Release dates
                $regions = ['eu', 'us', 'jp'];
                foreach ($regions as $region) {

                    $fieldReleaseDate = 'release_date_'.$region;
                    $fieldUpcomingDate = 'upcoming_date_'.$region;
                    $fieldIsReleased = 'is_released_'.$region;

                    $releaseDateValue = $feedItem{$fieldReleaseDate};
                    $upcomingDateValue = $feedItem{$fieldUpcomingDate};
                    $isReleasedValue = $feedItem{$fieldIsReleased};

                    $gameReleaseDateService->createGameReleaseDate($gameId, $region, $releaseDateValue, $isReleasedValue, $upcomingDateValue);

                }

                $this->info('Marking feed item as complete');
                $feedItem->game_id = $gameId;
                $feedItem->setStatusComplete();
                $feedItem->save();

                event(new GameCreated($game));

            }

        }
    }
}
