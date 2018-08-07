<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\CrawlerWikipediaGamesListSourceService;
use App\Services\GameService;
use App\Services\GameReleaseDateService;
use App\Services\GameTitleHashService;
use App\Services\FeedItemGameService;
use App\Services\HtmlLoader\Wikipedia\Importer;

class WikipediaImportGamesList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WikipediaImportGamesList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports feed items from Wikipedia crawl data, ready to update the games database.';

    /**
     * WikipediaImportGamesList constructor.
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

        $crawlerService = resolve('Services\CrawlerWikipediaGamesListSourceService');
        /* @var CrawlerWikipediaGamesListSourceService $crawlerService */
        $gameTitleHashService = resolve('Services\GameTitleHashService');
        /* @var GameTitleHashService $gameTitleHashService */
        $feedItemGameService = resolve('Services\FeedItemGameService');
        /* @var FeedItemGameService $feedItemGameService */
        $gameService = resolve('Services\GameService');
        /* @var GameService $gameService */
        $gameReleaseDateService = resolve('Services\GameReleaseDateService');
        /* @var GameReleaseDateService $gameReleaseDateService */

        $gamesList = $crawlerService->getAll();

        $this->info('Found '.count($gamesList).' item(s)');

        try {

            $importer = new Importer();

            $i = 0;

            foreach ($gamesList as $crawlerModel) {

                $i++;

                $this->line('Processing item: '.$i);

                //$this->info('Generating feed model');

                $importer->setCrawlerModel($crawlerModel);
                $feedItemGame = $importer->generateFeedModel();
                $title = $feedItemGame->item_title;

                // Discard games with all TBA dates
                if (($feedItemGame->upcoming_date_eu == 'TBA') &&
                    ($feedItemGame->upcoming_date_us == 'TBA') &&
                    ($feedItemGame->upcoming_date_jp == 'TBA')) {
                    $this->error($title.' - all dates are TBA - skipping');
                    continue;
                }

                // Discard games starting with Untitled
                $ignoreText = 'Untitled ';
                if (substr($title, 0, strlen($ignoreText)) == $ignoreText) {
                    $this->error('Ignoring Untitled entry: '. $title.' - skipping');
                    continue;
                }

                // See if we can locate the game
                $titleHash = $gameTitleHashService->generateHash($title);
                $gameTitleHash = $gameTitleHashService->getByHash($titleHash);
                if ($gameTitleHash) {
                    $gameId = $gameTitleHash->game_id;
                    $feedItemGame->game_id = $gameId;
                } else {
                    $gameId = null;
                }

                // Check if anything's changed since the last record, if one exists
                if ($gameId) {

                    // We need to skip anything that's already waiting to be reviewed.
                    // Otherwise, the list will keep adding duplicates each time the importer runs.
                    $activeFeedItem = $feedItemGameService->getActiveByGameId($gameId);
                    if ($activeFeedItem) {
                        $this->warn('Found an active, unprocessed entry for: '.$title.'; skipping');
                        continue;
                    }

                    // There isn't a previous entry in feed items, but has the game actually changed?
                    $game = $gameService->find($gameId);
                    $gameReleaseDates = $gameReleaseDateService->getByGame($gameId);
                    $modifiedFieldList = $importer->getGameModifiedFields($feedItemGame, $game, $gameReleaseDates);
                    if (count($modifiedFieldList) == 0) {
                        $this->line('No changes to game: '.$title.' [id: '.$gameId.']; skipping');
                        continue;
                    } else {
                        $this->info('Found changes to game: '.$title.' [id: '.$gameId.']');
                        $feedItemGame->modified_fields = serialize($modifiedFieldList);
                    }
                    /*
                    $lastFeedItem = $feedItemGameService->getLastEntryByGameId($gameId);
                    if ($lastFeedItem) {
                        $modifiedFieldList = $importer->getModifiedFields($feedItemGame, $lastFeedItem);
                        if (count($modifiedFieldList) == 0) {
                            $this->info('No changes to game: '.$title.' [id: '.$gameId.']; skipping');
                            continue;
                        } else {
                            $feedItemGame->modified_fields = serialize($modifiedFieldList);
                        }
                    }
                    */
                } else {
                    // If we don't know the game id and we haven't dealt with the imported data yet,
                    // duplicates will be added each time the importer is run.
                    // This check will stop those duplicates if an update is pending.
                    $activeFeedItem = $feedItemGameService->getActiveByTitle($title);
                    if ($activeFeedItem) {
                        $this->warn('Found an active, unprocessed entry for: '.$title.'; skipping');
                        continue;
                    }
                }

                $feedItemGame->save();

                //if ($i > 3) break;

            }

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
